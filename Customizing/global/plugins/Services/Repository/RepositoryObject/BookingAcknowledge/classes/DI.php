<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\BookingAcknowledge;

use CaT\Plugins\BookingAcknowledge\Utils\RequestDigester;
use CaT\Plugins\BookingAcknowledge\Mailing\Mailer;
use CaT\Plugins\BookingAcknowledge\Utils\CourseInfo;
use CaT\Plugins\BookingAcknowledge\Utils\UserInfo;
use CaT\Plugins\BookingAcknowledge\Utils\AccessHelper;
use ILIAS\TMS\Mailing\TMSMailClerk;

use Pimple\Container;

trait DI
{
    public function getObjectDI(
        BookingAcknowledge $object,
        \ArrayAccess $dic,
        \Closure $txt
    ) : Container {
        $container = new Container();
        $txt = $txt;

        $this->setupDBElements($container, $dic);
        $this->setupReportElements($container, $dic, $txt, $object);
        $this->setupGuiElements($container, $object, $dic, $txt);
        return $container;
    }

    protected function setupDBElements(
        Container $container,
        \ArrayAccess $dic
    ) {
        $container["db.acknowledge"] = $container->factory(
            function ($c) use ($dic) {
                return new Acknowledgments\ilDB($dic["ilDB"]);
            }
        );
        $container["mailer"] = $container->factory(
            function ($c) {
                require_once("./Services/TMS/Mailing/classes/ilTMSMailing.php");
                $mailing = new \ilTMSMailing();
                $tms_mail_clerk = $mailing->getClerk();
                return new Mailer($tms_mail_clerk);
            }
        );
        $container["info.course"] = $container->factory(
            function ($c) use ($dic) {
                return new CourseInfo($dic, $dic["ilObjDataCache"]);
            }
        );
        $container["info.user"] = $container->factory(
            function ($c) {
                return new UserInfo();
            }
        );
    }

    protected function setupGuiElements(
        Container $container,
        BookingAcknowledge $object,
        \ArrayAccess $dic,
        \Closure $txt
    ) {
        $container["gui.settings"] = $container->factory(
            function ($c) use ($object, $dic) {
                require_once __DIR__ . "/Settings/class.ilBookingAcknowledgeSettingsGUI.php";
                return new \ilBookingAcknowledgeSettingsGUI(
                    $dic["ilCtrl"],
                    $dic["tpl"],
                    $object
                );
            }
        );

        $container["gui.upcoming"] = $container->factory(
            function ($c) use ($object, $dic, $container, $txt) {
                require_once __DIR__ . "/Acknowledgments/class.ilAcknowledgmentUpcomingGUI.php";
                return new \ilAcknowledgmentUpcomingGUI(
                    $dic["ilCtrl"],
                    $dic["tpl"],
                    $container["report"],
                    $object,
                    $txt
                );
            }
        );

        $container["gui.finished"] = $container->factory(
            function ($c) use ($object, $dic, $container, $txt) {
                require_once __DIR__ . "/Acknowledgments/class.ilAcknowledgmentFinishedGUI.php";
                return new \ilAcknowledgmentFinishedGUI(
                    $dic["ilCtrl"],
                    $dic["tpl"],
                    $container["report"],
                    $object,
                    $txt
                );
            }
        );
    }

    protected function setupReportElements(
        Container $container,
        \ArrayAccess $dic,
        \Closure $txt,
        BookingAcknowledge $object
    ) {
        $container["actionlinks"] = $container->factory(
            function ($c) use ($dic, $digester, $container, $object) {
                return new ActionLinksHelper(
                    $dic,
                    $dic["ilCtrl"],
                    (int) $dic['ilUser']->getId(),
                    new RequestDigester(),
                    $container["info.course"],
                    $container["info.user"],
                    new AccessHelper($dic["ilAccess"], (int) $object->getRefId())
                );
            }
        );

        $container["userorgulocator"] = $container->factory(
            function ($c) use ($dic) {
                $orgu_tree = \ilObjOrgUnitTree::_getInstance();
                $pos_helper = new \TMSPositionHelper(\ilOrgUnitUserAssignmentQueries::getInstance());

                return new UserOrguLocator(
                    $orgu_tree,
                    $dic["ilAccess"],
                    $pos_helper
                );
            }
        );

        $container["report"] = $container->factory(
            function ($c) use ($dic, $txt, $container, $object) {
                $o_d = new \ilTreeObjectDiscovery($dic["tree"]);
                return new Report(
                    $dic["ilDB"],
                    $txt,
                    $container["actionlinks"],
                    $o_d,
                    $container["userorgulocator"],
                    $dic['ilUser'],
                    new AccessHelper($dic["ilAccess"], (int) $object->getRefId())
                );
            }
        );
    }
}
