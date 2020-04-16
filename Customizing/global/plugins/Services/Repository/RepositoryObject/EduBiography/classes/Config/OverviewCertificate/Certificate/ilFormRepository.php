<?php

declare(strict_types=1);

namespace CaT\Plugins\EduBiography\Config\OverviewCertificate\Certificate;

class ilFormRepository extends \ilCertificateSettingsFormRepository
{
    public function createForm(\ilCertificateGUI $certificateGUI)
    {
        global $DIC;
        $form = parent::createForm($certificateGUI);
        $form->addCommandButton("cancel", $DIC["lng"]->txt("back"));
        return $form;
    }
}
