<?php

namespace ILIAS\Setup;

/**
 * Class ImplementationOfInterfaceFinder
 *
 * @package ILIAS\ArtifactBuilder\Generators
 */
class ImplementationOfInterfaceFinder
{
    /**
     * @var string
     */
    private $interface;

    /**
     * @var array
     */
    private $ignore = [
        '.*/Customizing/',
        '.*/libs/',
        '.*/test/',
        '.*/tests/',
        '.*/setup/',
        // Classes using removed Auth-class from PEAR
        '.*ilSOAPAuth.*',
        // Classes using unknown
        '.*ilPDExternalFeedBlockGUI.*',
    ];

    public function __construct(string $interface)
    {
        $this->interface = $interface;
    }

    public function getIgnoreList() : array
    {
        return $this->ignore;
    }

    public function getMatchingClassNames(array $ignore = []) : \Iterator
    {
        if (count($ignore) === 0) {
            $ignore = $this->ignore;
        }

        foreach ($this->getAllClassNames($ignore) as $class_name) {
            try {
                $r = new \ReflectionClass($class_name);
                if ($r->isInstantiable() && $r->implementsInterface($this->interface)) {
                    yield $class_name;
                }
            } catch (\Throwable $e) {
                // noting to do here
            }
        }
    }

    protected function getAllClassNames() : \Iterator
    {
        // We use the composer classmap ATM
        $composer_classmap = include "./libs/composer/vendor/composer/autoload_classmap.php";
        $root = substr(__FILE__, 0, strpos(__FILE__, "/src"));

        if (!is_array($composer_classmap)) {
            throw new \LogicException("Composer ClassMap not loaded");
        }

        $regexp = implode(
            "|",
            array_map(
            // fix path-separators to respect windows' backspaces.
                function ($v) {
                    return "(" . str_replace('/', '(/|\\\\)', $v) . ")";
                },
                $ignore
            )
        );

        foreach ($composer_classmap as $class_name => $file_path) {
            $path = str_replace($root, "", realpath($file_path));
            if (!preg_match("#^" . $regexp . "$#", $path)) {
                yield $class_name;
            }
        }
    }
}
