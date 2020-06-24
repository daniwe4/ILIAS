<?php

namespace CaT\Plugins\MaterialList\RPC;

/**
 * Reads the procedure folder
 */
class FolderReader
{
    /**
     * @var \Closure
     */
    protected $txt;

    public function __construct(\Closure $txt)
    {
        $this->txt = $txt;
    }

    /**
     * Get select input options for custom function
     *
     * @param string 	$path
     *
     * @return string[]
     */
    public function getCustomFunctionOptions($path)
    {
        $files = $this->readFolder($path);
        return $this->transformFiles($files);
    }

    /**
     * Get select input options for course standard function
     *
     * @param string 	$path
     *
     * @return string[]
     */
    public function getCourseFunctionOptions($path)
    {
        $files = $this->readFolder($path);
        return $this->transformFiles($files);
    }

    /**
     * Read the path for files
     *
     * @param $path
     *
     * @return $files
     */
    protected function readFolder($path)
    {
        $files = array();
        foreach (scandir($path) as $file) {
            if ($file == "." || $file == "..") {
                continue;
            }

            $files[] = $this->cleanFileName($file);
        }

        return $files;
    }

    /**
     * Cut il and suffix from file name
     *
     * @param string 	$file
     *
     * @return string
     */
    protected function cleanFileName($file)
    {
        $file = substr($file, 2);
        $file = substr($file, 0, (strlen($file) - 4));
        return lcfirst($file);
    }

    /**
     * Get array with conform to ILIAS select input options
     *
     * @param string[]	$files
     *
     * @return array<string, string>
     */
    protected function transformFiles($files)
    {
        $ret = array();

        foreach ($files as $file) {
            $ret[$file] = $this->txt("fnc_" . $this->fromCamelCase($file));
        }

        return $ret;
    }

    /**
     * Change camel case to underscore splittet value
     *
     * @param string $name
     *
     * @return string
     */
    private function fromCamelCase($name)
    {
        return preg_replace_callback("/[A-Z]/", function ($matches) {
            return "_" . strtolower($matches[0]);
        }, $name);
    }

    /**
     * Translate code to lang value
     *
     * @param string 	$code
     *
     * @return string
     */
    protected function txt(string $code)
    {
        $txt = $this->txt;

        return $txt($code);
    }
}
