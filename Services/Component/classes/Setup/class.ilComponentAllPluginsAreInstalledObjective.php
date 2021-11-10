<?php declare(strict_types=1);

/* Copyright (c) 2021 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;
use ILIAS\DI;
use ILIAS\Setup\Objective\ClientIdReadObjective;

class ilComponentAllPluginsAreInstalledObjective implements Setup\Objective
{
    /**
     * @inheritdoc
     */
    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    /**
     * @inheritdoc
     */
    public function getLabel() : string
    {
        return "Ensure all plugins listed in db are installed.";
    }

    /**
     * @inheritdoc
     */
    public function isNotable() : bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getPreconditions(Setup\Environment $environment) : array
    {
        return [
            new ClientIdReadObjective(),
            new ilIniFilesPopulatedObjective(),
            new ilDatabaseUpdatedObjective(),
            new ilComponentPluginAdminInitObjective()
        ];
    }

    /**
     * @inheritdoc
     */
    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $ORIG_DIC = $this->initEnvironment($environment);

        $db = $GLOBALS["DIC"]["ilDB"];

        $sql =
            "SELECT name FROM il_plugin" . PHP_EOL
            . "WHERE" . PHP_EOL
            . "last_update_version IS NULL AND" . PHP_EOL
            . "active IS NULL AND" . PHP_EOL
            . "plugin_id IS NULL AND" . PHP_EOL
            . "db_version = 0" . PHP_EOL
        ;

        $result = $db->query($sql);

        while ($row = $db->fetchAssoc($result)) {
            $plugin = $GLOBALS["DIC"]["ilPluginAdmin"]->getRawPluginDataFor($row['name']);

            if (!is_null($plugin) && $plugin['must_install'] && $plugin['supports_cli_setup']) {
                $pl = ilPlugin::getPluginObject(
                    $plugin['component_type'],
                    $plugin['component_name'],
                    $plugin['slot_id'],
                    $plugin['name']
                );

                $pl->install();
                $pl->update();
                $pl->activate();
            }
        }

        $GLOBALS["DIC"] = $ORIG_DIC;

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment) : bool
    {
        return true;
    }

    protected function initEnvironment(Setup\Environment $environment) : ILIAS\DI\Container
    {
        $db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);
        $plugin_admin = $environment->getResource(Setup\Environment::RESOURCE_PLUGIN_ADMIN);
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);
        $client_ini = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI);


        // ATTENTION: This is a total abomination. It only exists to allow various
        // sub components of the various readers to run. This is a memento to the
        // fact, that dependency injection is something we want. Currently, every
        // component could just service locate the whole world via the global $DIC.
        $DIC = $GLOBALS["DIC"];
        $GLOBALS["DIC"] = new DI\Container();
        $GLOBALS["DIC"]["ilDB"] = $db;
        $GLOBALS["DIC"]["ilIliasIniFile"] = $ini;
        $GLOBALS["DIC"]["ilClientIniFile"] = $client_ini;
        $GLOBALS["DIC"]["ilLogger"] = new class() extends ilLogger {
            public function __construct()
            {
            }
            public function isHandling($a_level)
            {
                return true;
            }
            public function log($a_message, $a_level = ilLogLevel::INFO)
            {
            }
            public function dump($a_variable, $a_level = ilLogLevel::INFO)
            {
            }
            public function debug($a_message, $a_context = array())
            {
            }
            public function info($a_message)
            {
            }
            public function notice($a_message)
            {
            }
            public function warning($a_message)
            {
            }
            public function error($a_message)
            {
            }
            public function critical($a_message)
            {
            }
            public function alert($a_message)
            {
            }
            public function emergency($a_message)
            {
            }
            public function write($a_message, $a_level = ilLogLevel::INFO)
            {
            }
            public function writeLanguageLog($a_topic, $a_lang_key)
            {
            }
            public function logStack($a_level = null, $a_message = '')
            {
            }
            public function writeMemoryPeakUsage($a_level)
            {
            }
        };
        $GLOBALS["DIC"]["ilLog"] = new class() extends ilLog {
            public function __construct()
            {
            }
            public function write($m, $l = ilLogLevel::INFO)
            {
            }
            public function info($msg)
            {
            }
            public function warning($msg)
            {
            }
            public function error($msg)
            {
            }
            public function debug($msg, $a = [])
            {
            }
            public function dump($msg, $a = ilLogLevel::INFO)
            {
            }
        };
        $GLOBALS["DIC"]["ilLoggerFactory"] = new class() extends ilLoggerFactory {
            public function __construct()
            {
            }
            public static function getRootLogger()
            {
                return $GLOBALS["DIC"]["ilLogger"];
            }
            public static function getLogger($a)
            {
                return $GLOBALS["DIC"]["ilLogger"];
            }
        };
        $GLOBALS["ilLog"] = $GLOBALS["DIC"]["ilLog"];
        $GLOBALS["DIC"]["ilBench"] = null;
        $GLOBALS["DIC"]["lng"] = new ilLanguage('en');
        $GLOBALS["DIC"]["ilPluginAdmin"] = $plugin_admin;
        $GLOBALS["DIC"]["ilCtrl"] = new ilCtrl();
        $GLOBALS["DIC"]["ilias"] = null;
        $GLOBALS["DIC"]["ilErr"] = null;
        $GLOBALS["DIC"]["tree"] = null;
        $GLOBALS["DIC"]["ilAppEventHandler"] = null;
        $GLOBALS["DIC"]["ilSetting"] = new ilSetting();
        $GLOBALS["DIC"]["objDefinition"] = new ilObjectDefinition();
        $GLOBALS["DIC"]["ilUser"] = new class() extends ilObjUser {
            public $prefs = [];

            public function __construct()
            {
                $this->prefs["language"] = "en";
            }
        };

        if (!defined('DEBUG')) {
            define('DEBUG', false);
        }

        if (!defined('SYSTEM_ROLE_ID')) {
            define('SYSTEM_ROLE_ID', '2');
        }

        if (!defined("CLIENT_ID")) {
            define('CLIENT_ID', $client_ini->readVariable('client', 'name'));
        }

        if (!defined("ILIAS_WEB_DIR")) {
            define('ILIAS_WEB_DIR', dirname(__DIR__, 4) . "/data/");
        }

        return $DIC;
    }
}
