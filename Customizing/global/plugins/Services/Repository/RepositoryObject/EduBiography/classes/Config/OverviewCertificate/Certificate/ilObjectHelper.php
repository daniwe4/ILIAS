<?php

namespace CaT\Plugins\EduBiography\Config\OverviewCertificate\Certificate;

class ilObjectHelper extends \ilCertificateObjectHelper
{
    public function lookupType(int $objectId) : string
    {
        return "xebr";
    }
}
