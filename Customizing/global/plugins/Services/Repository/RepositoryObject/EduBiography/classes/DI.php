<?php

declare(strict_types=1);

namespace CaT\Plugins\EduBiography;

use Pimple\Container;
use CaT\Security\PluginLogin\ILIAS;

trait DI
{
    public function getObjectDIC(
        \ilObjEduBiography $object,
        \ArrayAccess $dic
    ) : Container {
        $container = new Container();

        $container["ilCtrl"] = function ($c) use ($dic) {
            return $dic["ilCtrl"];
        };
        $container["ilTabs"] = function ($c) use ($dic) {
            return $dic["ilTabs"];
        };
        $container["tpl"] = function ($c) use ($dic) {
            return $dic["tpl"];
        };
        $container["ilDB"] = function ($c) use ($dic) {
            return $dic["ilDB"];
        };
        $container["ilAccess"] = function ($c) use ($dic) {
            return $dic["ilAccess"];
        };
        $container["lng"] = function ($c) use ($dic) {
            return $dic["lng"];
        };
        $container["ilUser"] = function ($c) use ($dic) {
            return $dic["ilUser"];
        };
        $container["ilLog"] = function ($c) use ($dic) {
            return $dic["ilLog"];
        };
        $container["ilObjDataCache"] = function ($c) use ($dic) {
            return $dic["ilObjDataCache"];
        };
        $container["ilLoggerFactory"] = function ($c) use ($dic) {
            return $dic["ilLoggerFactory"];
        };
        $container["ui.factory"] = function ($c) use ($dic) {
            return $dic["ui.factory"];
        };
        $container["ui.renderer"] = function ($c) use ($dic) {
            return $dic["ui.renderer"];
        };
        $container["filesystem"] = function ($c) use ($dic) {
            return $dic["filesystem"];
        };

        $container["txtclosure"] = function ($c) use ($object) {
            return $object->txtClosure();
        };

        $container["settings.gui.db"] = function ($c) {
            return new Settings\SettingsRepository(
                $c["ilDB"]
            );
        };
        $container["settings.gui.link"] = function ($c) {
            require_once __DIR__ . "/Settings/class.ilEduBiographySettingsGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                "ilEduBiographySettingsGUI",
                \ilEduBiographySettingsGUI::CMD_VIEW,
                "",
                false,
                false
            );
        };
        $container['plugin'] = function ($c) {
            return new \ilEduBiographyPlugin();
        };
        $container["settings.gui"] = function ($c) use ($object) {
            require_once __DIR__ . "/Settings/class.ilEduBiographySettingsGUI.php";
            return new \ilEduBiographySettingsGUI(
                $object,
                $c['plugin'],
                $c["ilCtrl"],
                $c["tpl"],
                $c["lng"],
                $c["ilAccess"],
                $c["txtclosure"],
                $c["settings.gui.db"]
            );
        };

        $container["config.schedules.db"] = function ($c) {
            return new Config\OverviewCertificate\Schedules\ilDB(
                $c["ilDB"],
                $c["ilUser"]
            );
        };

        $container["config.certificate.userspecificvalues"] = function ($c) {
            return new Config\OverviewCertificate\Certificate\ilUserSpecificValues(
                $c["ilDB"]
            );
        };

        $container["certificate.gui"] = function ($c) {
            require_once __DIR__ . "/OverviewCertificate/class.ilCertificateDownloadGUI.php";
            return new \ilCertificateDownloadGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["txtclosure"],
                $c["certificate.table.gui"],
                $c["config.schedules.db"],
                $c["config.certificate.userspecificvalues"],
                $c["ilUser"],
                $c["certificate.handler"],
                $c["certificate.db"],
                $c["part.document.pdf.generator"],
                $c["part.document.filesystem"]
            );
        };

        $container["certificate.gui.link"] = function ($c) {
            require_once __DIR__ . "/OverviewCertificate/class.ilCertificateDownloadGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                'ilCertificateDownloadGUI',
                \ilCertificateDownloadGUI::CMD_SHOW_CERTIFICATES,
                "",
                false,
                false
            );
        };

        $container["certificate.table.gui"] = function ($c) {
            return new OverviewCertificate\ilCertificateTableGUI(
                $c["ui.factory"],
                $c["ui.renderer"],
                $c["txtclosure"],
                $c["ilUser"],
                $c["ilCtrl"]
            );
        };

        $container["il_certificate.replacement"] = function ($c) {
            return new \ilCertificateValueReplacement();
        };

        $container["il_certificate.user.repository"] = function ($c) {
            return new \ilUserCertificateRepository();
        };

        $container["il_certificate.template_repository"] = function ($c) {
            return new \ilCertificateTemplateRepository(
                $c["ilDB"],
                $c["ilLog"],
                $c["ilObjDataCache"]
            );
        };

        $container["config.certificate.pdfgenerator"] = function ($c) {
            return new Config\OverviewCertificate\Certificate\ilGenerator(
                $c["il_certificate.user.repository"],
                $c["il_certificate.logger"],
                $c["lng"]
            );
        };

        $container["il_certificate.logger"] = function ($c) {
            return $c["ilLoggerFactory"]->getComponentLogger("cert");
        };

        $container["il_certificate.util.helper"] = function ($c) {
            return new \ilCertificateUtilHelper();
        };

        $container["certificate.placeholder.values"] = function ($c) {
            return new Config\OverviewCertificate\Certificate\ilPlaceholderValues(
                $c["il_certificate.placeholder_values"],
                $c["config.schedules.db"],
                $c["ilDB"],
                $c["config.certificate.userspecificvalues"]
            );
        };

        $container["certificate.handler"] = function ($c) {
            return new OverviewCertificate\ilCertificateHandling(
                $c["ilUser"],
                $c["il_certificate.replacement"],
                $c["certificate.placeholder.values"],
                $c["il_certificate.user.repository"],
                $c["il_certificate.template_repository"],
                $c["config.certificate.pdfgenerator"],
                $c["il_certificate.logger"],
                $c["il_certificate.util.helper"]
            );
        };

        $container["il_certificate.placeholder_values"] = function ($c) {
            return new \ilDefaultPlaceholderValues();
        };

        $container["certificate.db"] = function ($c) {
            return new OverviewCertificate\ilDB(
                $c["ilDB"]
            );
        };

        $container["part.document.pdf.generator"] = function ($c) {
            return new ParticipationDocument\ilGenerator(
                $c["txtclosure"],
                $c["part.document.pdf.db"]
            );
        };

        $container["part.document.pdf.db"] = function ($c) {
            return new ParticipationDocument\ilDB(
                $c["ilDB"]
            );
        };

        $container["part.document.filesystem"] = function ($c) {
            return new Config\OverviewCertificate\ParticipationDocument\ilFileStorage(
                $c["filesystem"]->web()
            );
        };

        // GOA special hack for #3410
        $container["filestorage"] = function ($c) use ($object) {
            return new FileStorage\ilCertificateStorage(
                (int) $object->getId()
            );
        };
        // GOA special hack for #3410

        $container["config.activation.db"] = function ($c) {
            return new Config\OverviewCertificate\Activation\ilDB(
                $c["ilDB"]
            );
        };

        return $container;
    }

    public function getPluginDIC(
        \ilEduBiographyPlugin $plugin,
        \ArrayAccess $dic
    ) : Container {
        $container = new Container();

        $container["ilCtrl"] = function ($c) use ($dic) {
            return $dic["ilCtrl"];
        };
        $container["ilTabs"] = function ($c) use ($dic) {
            return $dic["ilTabs"];
        };
        $container["tpl"] = function ($c) use ($dic) {
            return $dic["tpl"];
        };
        $container["ilDB"] = function ($c) use ($dic) {
            return $dic["ilDB"];
        };
        $container["ilAccess"] = function ($c) use ($dic) {
            return $dic["ilAccess"];
        };
        $container["lng"] = function ($c) use ($dic) {
            return $dic["lng"];
        };
        $container["ilUser"] = function ($c) use ($dic) {
            return $dic["ilUser"];
        };
        $container["ilLoggerFactory"] = function ($c) use ($dic) {
            return $dic["ilLoggerFactory"];
        };
        $container["ilToolbar"] = function ($c) use ($dic) {
            return $dic["ilToolbar"];
        };
        $container["ilLog"] = function ($c) use ($dic) {
            return $dic["ilLog"];
        };
        $container["filesystem"] = function ($c) use ($dic) {
            return $dic["filesystem"];
        };

        $container["txtclosure"] = function ($c) use ($plugin) {
            return function ($code) use ($plugin) {
                return $plugin->txt($code);
            };
        };

        $container["directory"] = function ($c) use ($plugin) {
            return $plugin->getDirectory();
        };

        $container["config.schedules.db"] = function ($c) {
            return new Config\OverviewCertificate\Schedules\ilDB(
                $c["ilDB"],
                $c["ilUser"]
            );
        };

        $container["config.schedules.overview.link"] = function ($c) {
            require_once __DIR__
                . "/Config/OverviewCertificate/Schedules/class.ilScheduleOverviewGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                \ilScheduleOverviewGUI::class,
                \ilScheduleOverviewGUI::CMD_SHOW,
                "",
                false,
                false
            );
        };

        $container["config.schedules.overview.gui"] = function ($c) {
            require_once __DIR__
                . "/Config/OverviewCertificate/Schedules/class.ilScheduleOverviewGUI.php";
            return new \ilScheduleOverviewGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["ilToolbar"],
                $c["ilTabs"],
                $c["config.schedules.db"],
                $c["txtclosure"],
                $c["directory"],
                $c["config.schedules.gui"],
                $c["config.certificate.gui"],
                $c["config.schedules.add.link"],
                $c["config.schedules.edit.link"],
                $c["config.schedules.delete.link"],
                $c["config.certificate.edit.link"],
                $c["config.activation.gui"],
                $c["config.activation.gui.link"],
                $c["config.schedules.overview.link"],
                $c["config.part_document.gui.link"],
                $c["config.part_document.gui"]
            );
        };

        $container["config.schedules.gui"] = function ($c) {
            require_once __DIR__
                . "/Config/OverviewCertificate/Schedules/class.ilScheduleGUI.php";
            return new \ilScheduleGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["config.schedules.overview.link"],
                $c["config.schedules.db"],
                $c["txtclosure"]
            );
        };

        $container["config.schedules.add.link"] = function ($c) {
            require_once __DIR__
                . "/Config/OverviewCertificate/Schedules/class.ilScheduleGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                [
                    \ilScheduleOverviewGUI::class,
                    \ilScheduleGUI::class
                ],
                \ilScheduleGUI::CMD_CREATE_SCHEDULE,
                "",
                false,
                false
            );
        };

        $container["config.schedules.edit.link"] = function ($c) {
            require_once __DIR__
                . "/Config/OverviewCertificate/Schedules/class.ilScheduleGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                [
                    \ilScheduleOverviewGUI::class,
                    \ilScheduleGUI::class
                ],
                \ilScheduleGUI::CMD_EDIT_SCHEDULE,
                "",
                false,
                false
            );
        };

        $container["config.schedules.delete.link"] = function ($c) {
            require_once __DIR__
                . "/Config/OverviewCertificate/Schedules/class.ilScheduleGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                [
                    \ilScheduleOverviewGUI::class,
                    \ilScheduleGUI::class
                ],
                \ilScheduleGUI::CMD_DELETE_CONFIRM,
                "",
                false,
                false
            );
        };

        $container["config.certificate.gui"] = function ($c) {
            require_once __DIR__
                . "/Config/OverviewCertificate/Certificate/class.ilCertificateConfigurationGUI.php";
            return new \ilCertificateConfigurationGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["config.schedules.overview.link"],
                $c["config.schedules.db"],
                $c["config.certificate.gui.factory"]
            );
        };

        $container["config.certificate.edit.link"] = function ($c) {
            require_once __DIR__
                . "/Config/OverviewCertificate/Certificate/class.ilCertificateConfigurationGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                [
                    \ilScheduleOverviewGUI::class,
                    \ilCertificateConfigurationGUI::class,
                    \ilOverviewCertificateGUI::class
                ],
                \ilCertificateConfigurationGUI::CMD_SHOW,
                "",
                false,
                false
            );
        };

        $container["config.certificate.userspecificvalues"] = function ($c) {
            return new Config\OverviewCertificate\Certificate\ilUserSpecificValues(
                $c["ilDB"]
            );
        };

        $container["config.certificate.gui.factory"] = function ($c) use ($dic) {
            return new Config\OverviewCertificate\Certificate\CertificateGUIFactory(
                $c["ilDB"],
                $c["lng"],
                $c["tpl"],
                $c["ilCtrl"],
                $c["ilAccess"],
                $c["ilToolbar"],
                $c["ilLog"],
                $c["ilLoggerFactory"],
                $c["config.schedules.overview.link"],
                $c["txtclosure"],
                $c["config.schedules.db"],
                $c["config.certificate.userspecificvalues"],
                $dic->filesystem()->web()
            );
        };

        $container["admin.plugin.link"] = function ($c) {
            return $c["ilCtrl"]->getLinkTargetByClass(
                "ilobjcomponentsettingsgui",
                "view",
                "",
                false,
                false
            );
        };

        $container["config.activation.gui"] = function ($c) {
            require_once __DIR__
                . "/Config/OverviewCertificate/Activation/class.ilActivationGUI.php";
            return new \ilActivationGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["txtclosure"],
                $c["ilUser"],
                $c["config.activation.db"]
            );
        };

        $container["config.activation.gui.link"] = function ($c) {
            require_once __DIR__
                . "/Config/OverviewCertificate/Activation/class.ilActivationGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                [
                    \ilScheduleOverviewGUI::class,
                    \ilActivationGUI::class
                ],
                \ilActivationGUI::CMD_SHOW,
                "",
                false,
                false
            );
        };

        $container["config.activation.db"] = function ($c) {
            return new Config\OverviewCertificate\Activation\ilDB(
                $c["ilDB"]
            );
        };

        $container['security.gui.link'] = function ($c) use ($plugin) {
            require_once __DIR__ . '/Config/Security/class.ilXEBRSecurityGUI.php';
            return $c['ilCtrl']->getLinkTargetByClass(
                \ilXEBRSecurityGUI::class,
                \ilXEBRSecurityGUI::CMD_SHOW_CONFIG
            );
        };

        $container['security.db'] = function ($c) {
            return new ILIAS\ilDB(
                $c['ilDB']
            );
        };

        $container["plugin.id"] = function ($c) use ($plugin) {
            return $plugin->getId();
        };

        $container['security.gui'] = function ($c) use ($plugin) {
            require_once __DIR__ . '/Config/Security/class.ilXEBRSecurityGUI.php';
            return new \ilXEBRSecurityGUI(
                $c['ilCtrl'],
                $c['tpl'],
                $c['txtclosure'],
                $c['plugin.id'],
                $c['security.db']
            );
        };

        $container["part.document.filesystem"] = function ($c) {
            return new Config\OverviewCertificate\ParticipationDocument\ilFileStorage(
                $c["filesystem"]->web()
            );
        };

        $container["config.part_document.gui"] = function ($c) {
            require_once __DIR__
                . "/Config/OverviewCertificate/ParticipationDocument/class.ilParticipationDocumentGUI.php";
            return new \ilParticipationDocumentGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["txtclosure"],
                $c["part.document.filesystem"]
            );
        };

        $container["config.part_document.gui.link"] = function ($c) {
            require_once __DIR__
                . "/Config/OverviewCertificate/ParticipationDocument/class.ilParticipationDocumentGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                [
                    \ilScheduleOverviewGUI::class,
                    \ilParticipationDocumentGUI::class
                ],
                \ilParticipationDocumentGUI::CMD_SHOW_CONFIG,
                "",
                false,
                false
            );
        };

        return $container;
    }
}
