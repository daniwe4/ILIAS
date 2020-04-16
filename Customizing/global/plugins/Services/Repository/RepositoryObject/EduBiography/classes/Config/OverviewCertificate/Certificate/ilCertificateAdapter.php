<?php

declare(strict_types=1);

namespace CaT\Plugins\EduBiography\Config\OverviewCertificate\Certificate;

class ilCertificateAdapter extends \ilCertificateAdapter
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var ilPlaceholderDescription
     */
    protected $placeholder_description;

    /**
     * @var ilPlaceholderValues
     */
    protected $placeholder_values;

    public function __construct(
        int $id,
        ilPlaceholderDescription $placeholder_description,
        ilPlaceholderValues $placeholder_values
    ) {
        $this->id = $id;
        $this->placeholder_description = $placeholder_description;
        $this->placeholder_values = $placeholder_values;
        parent::__construct();
    }


    /**
     * @inheritDoc
     */
    public function getCertificatePath()
    {
        return \ilEduBiographyPlugin::CERTIFICATE_PATH
            . $this->id
            . '/';
    }

    /**
     * @inheritDoc
     */
    public function getCertificateVariablesForPreview()
    {
        $vars = $this->placeholder_values->getPlaceholderValuesForPreview(0, $this->id);

        $insert_tags = array();
        foreach ($vars as $id => $caption) {
            $insert_tags["[" . $id . "]"] = $caption;
        }
        return $insert_tags;
    }

    /**
     * @inheritDoc
     */
    public function getCertificateVariablesForPresentation($params = array())
    {
        $user_id = $params["user_id"];

        $vars = $this->placeholder_values->getPlaceholderValues($user_id, $this->id);

        $insert_tags = array();
        foreach ($vars as $id => $caption) {
            $insert_tags["[" . $id . "]"] = $caption;
        }
        return $insert_tags;
    }

    /**
     * @inheritDoc
     */
    public function getCertificateVariablesDescription()
    {
        return $this->placeholder_description->createPlaceholderHtmlDescription();
    }

    /**
     * @inheritDoc
     */
    public function getAdapterType()
    {
        return "xebr";
    }

    /**
     * @inheritDoc
     */
    public function getCertificateID()
    {
        return $this->id;
    }
}
