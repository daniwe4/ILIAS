<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\CourseMailing;

use Pimple\Container;
use CaT\Libs\ExcelWrapper\Spout;

trait DI
{
    public function getObjectDIC(
        \ilObjCourseMailing $object,
        \ArrayAccess $dic
    ) : Container {
        $container = new Container();

        $container["ilDB"] = function ($c) use ($dic) {
            return $dic["ilDB"];
        };

        $container["ilCtrl"] = function ($c) use ($dic) {
            return $dic["ilCtrl"];
        };

        $container["tpl"] = function ($c) use ($dic) {
            return $dic["tpl"];
        };

        $container["lng"] = function ($c) use ($dic) {
            return $dic["lng"];
        };

        $container["ilAccess"] = function ($c) use ($dic) {
            return $dic["ilAccess"];
        };

        $container["ilUser"] = function ($c) use ($dic) {
            return $dic["ilUser"];
        };

        $container["ui.factory"] = function ($c) use ($dic) {
            return $dic["ui.factory"];
        };

        $container["ui.renderer"] = function ($c) use ($dic) {
            return $dic["ui.renderer"];
        };

        $container["ilToolbar"] = function ($c) use ($dic) {
            return $dic["ilToolbar"];
        };

        $container["txtclosure"] = function ($c) use ($object) {
            return $object->txtClosure();
        };

        $container["plugindir"] = function ($c) use ($object) {
            return $object->getPluginDirectory();
        };

        $container["umail"] = function ($c) {
            return new \ilFormatMail(
                $c["ilUser"]->getId()
            );
        };

        $container["mfile"] = function ($c) {
            return new \ilFileDataMail(
                $c["ilUser"]->getId()
            );
        };

        $container["surroundings"] = function ($c) {
            return new Surroundings\Surroundings(
                $c["surroundings.crsaccessor"],
                $c["surroundings.mailsaccessor"]
            );
        };

        $container["surroundings.crsaccessor"] = function ($c) use ($object) {
            return new Surroundings\ilCourseAccessor((int) $object->getRefId());
        };

        $container["surroundings.mailsaccessor"] = function ($c) {
            return new Surroundings\ilMailsAccessor();
        };

        $container["log.db"] = function ($c) {
            require_once("Services/TMS/Mailing/classes/class.ilTMSMailingLogsDB.php");
            return new \ilTMSMailingLogsDB(
                $c["ilDB"]
            );
        };

        $container["log.gui.link"] = function ($c) {
            require_once __DIR__ . "/Logging/class.ilMailLogsGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                "ilMailLogsGUI",
                \ilMailLogsGUI::CMD_SHOW,
                "",
                false,
                false
            );
        };

        $container["log.gui"] = function ($c) {
            require_once __DIR__ . "/Logging/class.ilMailLogsGUI.php";
            return new \ilMailLogsGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["ilAccess"],
                $c["ui.factory"],
                $c["ui.renderer"],
                $c["surroundings"],
                $c["log.db"],
                $c["txtclosure"]
            );
        };

        $container["membermail.gui"] = function ($c) {
            require_once __DIR__ . "/MemberMail/class.ilMemberMailGUI.php";
            return new \ilMemberMailGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["lng"],
                $c["ilUser"],
                $c["umail"],
                $c["mfile"],
                $c["surroundings"],
                $c["txtclosure"],
                $c["clerk"],
                $c["plugindir"],
                $c["mapping.roles"],
                $c["membermail.attachment.gui"]
            );
        };

        $container["invites.gui"] = function ($c) {
            require_once __DIR__ . "/Invites/class.ilInvitesGUI.php";
            return new \ilInvitesGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["ilToolbar"],
                $c["txtclosure"],
                $c["invites.db"],
                $c["surroundings"],
                $c["plugindir"],
                $c["ilUser"],
                $c["ilAccess"]
            );
        };

        $container["membermail.gui.link"] = function ($c) {
            require_once __DIR__ . "/MemberMail/class.ilMemberMailGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                "ilMemberMailGUI",
                \ilMemberMailGUI::CMD_SHOW_MEMBERS,
                "",
                false,
                false
            );
        };

        $container["invites.gui.link"] = function ($c) {
            require_once __DIR__ . "/Invites/class.ilInvitesGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                "ilInvitesGUI",
                \ilInvitesGUI::CMD_VIEW_INVITED,
                "",
                false,
                false
            );
        };

        $container["invites.gui.static.link"] = function ($c) use ($object) {
            require_once __DIR__ . "/Invites/class.ilInvitesGUI.php";
            return $link = \ilLink::_getLink(
                $object->getRefId(),
                $object->getType(),
                array("cmd" => \ilInvitesGUI::CMD_VIEW_INVITED)
            );
        };

        $container["invites.db"] = function ($c) {
            return new Invites\ilDB(
                $c["ilDB"]
            );
        };

        $container["clerk"] = function ($c) {
            return $c["mailing"]->getClerk();
        };

        $container["mailing"] = function ($c) {
            return new \ilTMSMailing();
        };

        $container["mapping.roles"] = function ($c) use ($object) {
            return $object->getRoleMappings();
        };

        $container["mapping.db"] = function ($c) use ($object) {
            return new RoleMapping\ilDB(
                $c['ilDB']
            );
        };

        $container["membermail.attachment.gui"] = function ($c) use ($dic) {
            $old_tpl = $dic["tpl"];
            unset($dic["tpl"]);
            $dic["tpl"] = $c["tmstemplate"];

            $gui = new \ilMailAttachmentGUI();

            unset($dic["tpl"]);
            $dic["tpl"] = $old_tpl;
            return $gui;
        };

        $container["tmstemplate"] = function ($c) {
            return new \TMSTemplate(
                $c["tpl"]
            );
        };

        return $container;
    }

    public function getPluginDIC(
        \ilCourseMailingPlugin $plugin,
        \ArrayAccess $dic
    ) : Container {
        $container = new Container();

        $container["ilDB"] = function ($c) use ($dic) {
            return $dic["ilDB"];
        };

        $container["invites.db"] = function ($c) {
            return new Invites\ilDB(
                $c["ilDB"]
            );
        };

        $container["log.db"] = function ($c) {
            require_once("Services/TMS/Mailing/classes/class.ilTMSMailingLogsDB.php");
            return new \ilTMSMailingLogsDB(
                $c["ilDB"]
            );
        };

        $container["mapping.db"] = function ($c) {
            return new RoleMapping\ilDB(
                $c['ilDB']
            );
        };

        return $container;
    }
}
