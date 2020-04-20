<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Filter\Factory as FilterFactory;
use PHPUnit\Runner\Filter\ExcludeGroupFilterIterator as GroupExcludeFilter;

/**
* This is the global ILIAS test suite. It searches automatically for
* components test suites by scanning all Modules/.../test and
* Services/.../test directories for test suite files.
*
* Test suite files are identified automatically, if they are named
* "ilServices[ServiceName]Suite.php" or ilModules[ModuleName]Suite.php".
*
* @author	<alex.killing@gmx.de>
*/
class ilGlobalSuite extends TestSuite
{
    /**
     * @var	string
     */
    const PHPUNIT_GROUP_FOR_TESTS_REQUIRING_INSTALLED_ILIAS = "needsInstalledILIAS";
    const REGEX_TEST_FILENAME = "#[a-zA-Z]+Test\.php#";
    const PHP_UNIT_PARENT_CLASS = TestCase::class;

    /**
     * Check if there is an installed ILIAS to run tests on.
     *
     * TODO: implement me correctly!
     * @return	bool
     */
    public function hasInstalledILIAS()
    {
        $ilias_ini_path = __DIR__ . "/../../../ilias.ini.php";

        if (!is_file($ilias_ini_path)) {
            return false;
        }
        require_once './Services/Init/classes/class.ilIniFile.php';
        $ilias_ini = new ilIniFile($ilias_ini_path);
        $ilias_ini->read();
        $client_data_path = $ilias_ini->readVariable("server", "absolute_path") . "/" . $ilias_ini->readVariable("clients", "path");

        if (!is_dir($client_data_path)) {
            return false;
        }

        include_once($ilias_ini->readVariable("server", "absolute_path") . "/Services/PHPUnit/config/cfg.phpunit.php");

        if (!isset($_GET["client_id"])) {
            return false;
        }

        $phpunit_client = $_GET["client_id"];

        if (!$phpunit_client) {
            return false;
        }

        if (!is_file($client_data_path . "/" . $phpunit_client . "/client.ini.php")) {
            return false;
        }

        return true;
    }

    public static function suite()
    {
        $suite = new ilGlobalSuite();
        echo "ILIAS PHPUnit-Tests need installed dev-requirements, please install using 'composer install' in ./libs/composer \n";
        echo "\n";

        // cat tms-patch start
        // scan Modules and Services directories
        $basedirs = array("Services", "Modules", "CronHook", "EventHook", "OrgUnitTypeHook", "RepositoryObject");

        foreach ($basedirs as $basedir) {
            $test = "tests";
            switch ($basedir) {
                case "CronHook":
                    $path = "Customizing/global/plugins/Services/Cron/" . $basedir;
                    $dir = opendir($path);
                    break;
                case "EventHook":
                    $path = "Customizing/global/plugins/Services/EventHandling/" . $basedir;
                    $dir = opendir($path);
                    break;
                case "OrgUnitTypeHook":
                    $path = "Customizing/global/plugins/Services/OrgUnit/" . $basedir;
                    $dir = opendir($path);
                    break;
                case "RepositoryObject":
                    $path = "Customizing/global/plugins/Services/Repository/" . $basedir;
                    $dir = opendir($path);
                    break;
                default:
                    $path = $basedir;
                    // read current directory
                    $dir = opendir($basedir);
                    $test = "test";
            }

            while ($file = readdir($dir)) {
                if ($file != "." && $file != ".." && is_dir($path . "/" . $file)) {
                    $suite_path =
                        $path . "/" . $file . "/" . $test . "/il" . $basedir . $file . "Suite.php";
                    if (is_file($suite_path)) {
                        include_once($suite_path);

                        $name = "il" . $basedir . $file . "Suite";
                        $s = $name::suite();
                        echo "Adding Suite: " . $name . "\n";
                        $suite->addTest($s);
                        //$suite->addTestSuite("ilSettingTest");
                    }
                }
            }
        }
        // cat tms-patch end

        $suite = self::addTestFolderToSuite($suite);

        echo "\n";

        if (!$suite->hasInstalledILIAS()) {
            echo "Removing tests requiring an installed ILIAS.\n";
            $ff = new FilterFactory();
            $ff->addFilter(
                new ReflectionClass(GroupExcludeFilter::class),
                array(self::PHPUNIT_GROUP_FOR_TESTS_REQUIRING_INSTALLED_ILIAS)
            );
            $suite->injectFilter($ff);
        } else {
            echo "Found installed ILIAS, running all tests.\n";
        }
        return $suite;
    }

    /**
     * Find and add all testSuits beneath ILIAS_ROOL/tests - folder
     *
     * @param	ilGlobalSuite	$suite
     * @return	ilGloblaSuite	$suite
     */
    protected static function addTestFolderToSuite(ilGlobalSuite $suite)
    {
        $test_directories = array("tests");
        while ($aux_dir = current($test_directories)) {
            if ($handle = opendir($aux_dir)) {
                $aux_dir .= DIRECTORY_SEPARATOR;
                while (false !== ($entry = readdir($handle))) {
                    if ($entry === '.' || $entry === '..') {
                        continue;
                    }
                    if (is_dir($aux_dir . $entry)) {
                        $test_directories[] = $aux_dir . $entry;
                    } else {
                        if (1 === preg_match(self::REGEX_TEST_FILENAME, $entry)) {
                            $ref_declared_classes = get_declared_classes();
                            require_once $aux_dir . "/" . $entry;
                            $new_declared_classes = array_diff(get_declared_classes(), $ref_declared_classes);
                            foreach ($new_declared_classes as $entry_class) {
                                $reflection = new ReflectionClass($entry_class);
                                if (!$reflection->isAbstract() && $reflection->isSubclassOf(self::PHP_UNIT_PARENT_CLASS)) {
                                    echo "Adding Test-Suite: " . $entry_class . "\n";
                                    $suite->addTestSuite($entry_class);
                                }
                            }
                        }
                    }
                }
            }
            next($test_directories);
        }
        return $suite;
    }
}
