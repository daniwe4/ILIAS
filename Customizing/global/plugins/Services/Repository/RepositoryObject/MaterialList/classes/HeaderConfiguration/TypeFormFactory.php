<?php

namespace CaT\Plugins\MaterialList\HeaderConfiguration;

class TypeFormFactory
{
    /**
     * @var Closure
     */
    protected $txt;

    /**
     * @var string[]
     */
    protected $standard_options;

    /**
     * @var string[]
     */
    protected $amd_options;

    /**
     * @var string[]
     */
    protected $function_options;

    /**
     * @param array<string, string> 	$standard_options
     * @param array<int, string> 		$amd_options
     * @param array<string, string> 	$function_options
     */
    public function __construct(\Closure $txt, array $standard_options, array $amd_options, array $function_options)
    {
        $this->txt = $txt;
        $this->standard_options = $standard_options;
        $this->amd_options = $amd_options;
        $this->function_options = $function_options;
    }

    /**
     * Get instance of TypeForm by type id
     *
     * @param string 	$type
     *
     * @return \CaT\Plugins\MaterialList\HeaderConfiguration\TypeForms\ilTypeForm
     */
    public function getTypeFormByType($type)
    {
        switch ($type) {
            case \CaT\Plugins\MaterialList\HeaderConfiguration\ConfigurationEntry::TYPE_STANDARD:
                return $this->getStandardForm();
                break;
            case \CaT\Plugins\MaterialList\HeaderConfiguration\ConfigurationEntry::TYPE_AMD:
                return $this->getAMDForm();
                break;
            case \CaT\Plugins\MaterialList\HeaderConfiguration\ConfigurationEntry::TYPE_FUNCTION:
                return $this->getFunctionForm();
                break;
            default:
                throw new \Exception(__METHOD__ . " unkown  type: " . $type);
        }
    }

    /**
     * Get the the type form for standard
     *
     * @return \CaT\Plugins\MaterialList\HeaderConfiguration\TypeForms\ilTypeFormStandard
     */
    protected function getStandardForm()
    {
        return new TypeForms\ilTypeFormStandard($this->txt, $this->standard_options);
    }

    /**
     * Get the the type form for standard
     *
     * @return \CaT\Plugins\MaterialList\HeaderConfiguration\TypeForms\ilTypeFormAMD
     */
    protected function getAMDForm()
    {
        return new TypeForms\ilTypeFormAMD($this->txt, $this->amd_options);
    }

    /**
     * Get the the type form for standard
     *
     * @return \CaT\Plugins\MaterialList\HeaderConfiguration\TypeForms\ilTypeFormFunction
     */
    protected function getFunctionForm()
    {
        return new TypeForms\ilTypeFormFunction($this->txt, $this->function_options);
    }
}
