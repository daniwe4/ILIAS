<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\TrainerOperations;

use CaT\Plugins\TrainerOperations\AccessHelper;
use CaT\Plugins\TrainerOperations\Settings;
use CaT\Plugins\TrainerOperations\Aggregations;
use CaT\Plugins\TrainerOperations\UserSettings;
use CaT\Plugins\TrainerOperations\Calendar;
use ILIAS\TMS\CourseCreation\ilCourseTemplateDB;
use Pimple\Container;

trait DI
{
    public function getObjectDI(
        ObjTrainerOperations $object,
        \ArrayAccess $dic,
        \Closure $txt
    ) : Container {
        $container = new Container();

        $container['dic.ilDB'] = function ($c) use ($dic) {
            return $dic["ilDB"];
        };
        $container['dic.tree'] = function ($c) use ($dic) {
            return $dic["tree"];
        };
        $container['dic.objDefinition'] = function ($c) use ($dic) {
            return $dic["objDefinition"];
        };
        $container['dic.ilAccess'] = function ($c) use ($dic) {
            return $dic["ilAccess"];
        };
        $container['dic.ilCtrl'] = function ($c) use ($dic) {
            return $dic["ilCtrl"];
        };
        $container['dic.rbacreview'] = function ($c) use ($dic) {
            return $dic["rbacreview"];
        };
        $container['dic.lng'] = function ($c) use ($dic) {
            return $dic["lng"];
        };
        $container['dic.ilUser'] = function ($c) use ($dic) {
            return $dic["ilUser"];
        };
        $container['dic.ui.factory'] = function ($c) use ($dic) {
            return $dic["ui.factory"];
        };
        $container['dic.ui.renderer'] = function ($c) use ($dic) {
            return $dic["ui.renderer"];
        };
        $container['dic.ilToolbar'] = function ($c) use ($dic) {
            return $dic["ilToolbar"];
        };
        $container['dic.ilTemplate'] = function ($c) use ($dic) {
            return $dic["tpl"];
        };

        $container['tep.objId'] = function ($c) use ($object) {
            return (int) $object->getId();
        };

        $container['tep.refId'] = function ($c) use ($object) {
            return (int) $object->getRefId();
        };

        $container['tep.pluginPath'] = function ($c) use ($object) {
            return $object->getPlugin()->getDirectory();
        };

        $container['tep.currentUserId'] = function ($c) {
            return (int) $c['dic.ilUser']->getId();
        };

        $container['tep.rbacimpl'] = function ($c) {
            return new \RbacImpl($c['dic.rbacreview']);
        };

        $container["db.settings"] = function ($c) {
            return new Settings\ilDB(
                $c['dic.ilDB']
            );
        };

        $container["db.usersettings"] = function ($c) {
            return new UserSettings\ilDB(
                $c['dic.ilDB']
            );
        };

        $container["db.coursetemplates"] = function ($c) {
            return new ilCourseTemplateDB(
                $c['dic.tree'],
                $c['dic.objDefinition']
            );
        };

        $container["repo.calsettings"] = function ($c) {
            return new UserSettings\CalSettingsRepository(
                $c["db.usersettings"],
                $c["il.calendars"],
                $c["utils.access"]
            );
        };


        $container["il.repository"] = function ($c) {
            return new Aggregations\IliasRepository(
                $c['dic.tree'],
                $c['dic.objDefinition'],
                $c['dic.ilDB']
            );
        };

        $container["il.calendars"] = function ($c) {
            require_once __DIR__ . "/Aggregations/IliasCalendar/CalendarRepository.php";
            return new Aggregations\IliasCalendar\CalendarRepository(
                $c['dic.ilDB']
            );
        };

        $container["il.roles"] = function ($c) {
            return new Aggregations\Roles(
                $c['dic.rbacreview']
            );
        };

        $container["il.user"] = function ($c) {
            return new Aggregations\User(
                $c['dic.ilUser']
            );
        };

        $container["tms.userauthority"] = function ($c) {
            require_once("Services/TMS/Positions/TMSPositionHelper.php");
            require_once("Modules/OrgUnit/classes/Positions/UserAssignment/class.ilOrgUnitUserAssignmentQueries.php");
            $orgu_queries = \ilOrgUnitUserAssignmentQueries::getInstance();
            $pos_helper = new \TMSPositionHelper($orgu_queries);
            return new Aggregations\UserAuthority(
                $pos_helper,
                $c['dic.rbacreview'],
                $c['db.settings'],
                $c['tep.objId'],
                $c['tep.currentUserId']
            );
        };

        $container["utils.access"] = function ($c) {
            return new AccessHelper(
                $c['dic.ilAccess'],
                $c['dic.ilCtrl'],
                $c['tep.refId'],
                $c['tep.currentUserId']
            );
        };

        $container["assingment"] = function ($c) {
            return new Calendar\AssignmentActions(
                $c["il.repository"]
            );
        };


        $container["cal.repo.sessions"] = function ($c) {
            require_once __DIR__ . "/Calendar/SessionEntryRepository.php";
            return new Calendar\SessionEntryRepository(
                $c['il.repository']
            );
        };

        $container["cal.repo.ilentries"] = function ($c) {
            require_once __DIR__ . "/Calendar/IliasEntryRepository.php";
            return new Calendar\IliasEntryRepository(
                $c["il.calendars"],
                $c["repo.calsettings"]
            );
        };

        $container["cal.builder"] = function ($c) use ($txt) {
            require_once __DIR__ . "/Calendar/CalendarBuilder.php";
            return new Calendar\CalendarBuilder(
                $txt,
                $c["il.user"],
                $c['cal.repo.sessions'],
                $c['cal.repo.ilentries'],
                $c['utils.access']
            );
        };

        $container["cal.renderer"] = function ($c) use ($txt) {
            $cal_tpl = new \ilTemplate('tpl.calendar.html', true, true, $c['tep.pluginPath']);
            $cell_tpl = new \ilTemplate('tpl.cell.html', true, true, $c['tep.pluginPath']);
            $event_tpl = new \ilTemplate('tpl.event.html', true, true, $c['tep.pluginPath']);
            $session_form_tpl = new \ilTemplate('tpl.session_form.html', true, true, $c['tep.pluginPath']);
            require_once __DIR__ . "/Calendar/CalRenderer.php";
            return new Calendar\CalRenderer(
                $txt,
                $cal_tpl,
                $cell_tpl,
                $event_tpl,
                $c['dic.ui.factory'],
                $c['dic.ui.renderer'],
                $c['tep.currentUserId']
            );
        };

        $container["cal.modal.session"] = function ($c) use ($txt) {
            $session_form_tpl = new \ilTemplate('tpl.session_form.html', true, true, $c['tep.pluginPath']);
            $tutor_add_form_tpl = new \ilTemplate('tpl.add_tutor_form.html', true, true, $c['tep.pluginPath']);
            require_once __DIR__ . "/Calendar/SessionModal.php";
            return new Calendar\SessionModal(
                $txt,
                $session_form_tpl,
                $tutor_add_form_tpl,
                $c['dic.ui.factory'],
                $c['dic.ui.renderer'],
                $c["il.repository"],
                $c["tms.userauthority"],
                $c["il.user"],
                $c["dic.ilCtrl"],
                $c["tep.currentUserId"],
                $c["utils.access"]
            );
        };

        $container["gui.settings"] = function ($c) use ($txt, $object) {
            $properties_form = new \ilPropertyFormGUI();
            require_once __DIR__ . "/Settings/class.ilTrainerOperationsSettingsGUI.php";
            return new \ilTrainerOperationsSettingsGUI(
                $c["utils.access"],
                $txt,
                $properties_form,
                $c["dic.ilCtrl"],
                $c["dic.ilTemplate"],
                $object,
                $c["il.roles"]
            );
        };

        $container["gui.calendar"] = function ($c) use ($txt) {
            $col_selector_tpl = new \ilTemplate('tpl.col_selector_form.html', true, true, $c['tep.pluginPath']);
            require_once __DIR__ . "/Calendar/class.ilTrainerOperationsGUI.php";
            return new \ilTrainerOperationsGUI(
                $txt,
                $c["dic.ilCtrl"],
                $c["dic.ilTemplate"],
                $c["dic.ilToolbar"],
                $c['dic.ui.factory'],
                $c['dic.ui.renderer'],
                $c['cal.renderer'],
                $c["cal.modal.session"],
                $c['cal.builder'],
                $c['tms.userauthority'],
                $c['il.repository'],
                $c["assingment"],
                $c["il.user"],
                $col_selector_tpl,
                $c['tep.objId'],
                $c['tep.refId'],
                $c["db.coursetemplates"],
                $c["dic.ilUser"],
                $c["dic.lng"],
                $c["utils.access"],
                $c['tep.rbacimpl']
            );
        };

        $container["gui.calsettings"] = function ($c) use ($txt, $object) {
            require_once __DIR__ . "/UserSettings/class.ilTrainerOperationsCalSettingsGUI.php";
            require_once __DIR__ . "/UserSettings/CalSettingsTableGUI.php";
            $gui = new \ilTrainerOperationsCalSettingsGUI(
                $c["utils.access"],
                $txt,
                $c['dic.ilCtrl'],
                $c['dic.ilTemplate'],
                $c['dic.ilToolbar'],
                $c['dic.ui.factory'],
                $c['dic.ui.renderer'],
                $c['repo.calsettings'],
                $c['tep.objId']
            );

            $table = new UserSettings\CalSettingsTableGUI($gui);
            $table->setRowTemplate('tpl.cal_settings_row.html', $c['tep.pluginPath']);
            $gui->setTableGUI($table);
            return $gui;
        };

        $container["gui.editcalendar"] = function ($c) use ($txt) {
            require_once __DIR__ . "/UserSettings/class.ilEditCalendarGUI.php";
            return new \ilEditCalendarGUI(
                $txt,
                $c['dic.ilCtrl'],
                $c['dic.ilTemplate'],
                $c["il.calendars"],
                $c["utils.access"]
            );
        };

        return $container;
    }
}
