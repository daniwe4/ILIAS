<?php

namespace CaT\Plugins\MaterialList\RPC;

class ProcedureLoader
{
    const ILIAS_CLASS_PREFIX = "il";
    const PROCEDURE_NAMESPACE = "\\CaT\\Plugins\\MaterialList\\RPC\\Procedures";

    /**
     * @var string
     */
    protected $base_path;

    /**
     * @var \Closure
     */
    protected $txt;


    public function __construct(string $base_path, \Closure $txt)
    {
        $this->base_path = $base_path;
        $this->txt = $txt;
    }

    /**
     * Get path of file name
     *
     * @param string $module
     * @param string $class_name
     *
     * @return string
     */
    protected function filePath($module, $class_name)
    {
        return $this->base_path . '/' . $module . '/' . $class_name . '.php';
    }

    /**
     * Check procedure file exists
     *
     * @param string $file_path
     *
     * @return bool
     */
    public function procedureExists($file_path)
    {
        return is_file($file_path);
    }

    /**
     * Load procedure
     *
     * @param string 			$module
     * @param string 			$procedure
     * @param \ilObjCourse 		$crs
     *
     * @return ilFunctionBase
     */
    public function loadProcedure($module, $procedure, \ilObjCourse $crs)
    {
        $class_name = $this->getClassName($procedure);
        $file_path = $this->filePath($module, $class_name);

        if (!$this->procedureExists($file_path)) {
            echo $file_path;
            throw new \InvalidArgumentException('procedure ' . $class_name . ' not found.');
        }

        $f = self::PROCEDURE_NAMESPACE . '\\' . $module . '\\' . $class_name;

        return new $f($crs, $this->txt);
    }

    /**
     * Get full class name
     *
     * @param string 	$procedure
     *
     * @return string
     */
    protected function getClassName($procedure)
    {
        return self::ILIAS_CLASS_PREFIX . ucfirst($procedure);
    }
}
