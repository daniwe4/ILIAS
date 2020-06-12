<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\DataChanges;

use CaT\Plugins\DataChanges\Config\DataChangeHelper;
use ilCertificateCron;
use ilCertificateQueueRepository;
use ilCertificateTemplateRepository;
use ilCertificateTypeClassMap;
use ilSetting;
use ilUserAutoComplete;
use Pimple\Container;
use CaT\Security\PluginLogin\ILIAS;
use CaT\Plugins\DataChanges\Config\RemoveUserFromCourse\ilDB;
use CaT\Plugins\DataChanges\Config\Log\ilDB as LogDB;
use CaT\Plugins\DataChanges\Config\MergeUsers\ilDB as MergeUserDB;

trait DI
{
    public function getPluginDIC(
        \ilDataChangesPlugin $plugin,
        \ArrayAccess $dic
    ) : Container {
        $container = new Container();

        $container['ilCtrl'] = function ($c) use ($dic) {
            return $dic['ilCtrl'];
        };

        $container['tpl'] = function ($c) use ($dic) {
            return $dic['tpl'];
        };

        $container['ilUser'] = function ($c) use ($dic) {
            return $dic['ilUser'];
        };

        $container['ilTabs'] = function ($c) use ($dic) {
            return $dic['ilTabs'];
        };

        $container['ilDB'] = function ($c) use ($dic) {
            return $dic['ilDB'];
        };

        $container['ilLog'] = function ($c) use ($dic) {
            return $dic['ilLog'];
        };

        $container['rbacadmin'] = function ($c) use ($dic) {
            return $dic['rbacadmin'];
        };

        $container['rbacreview'] = function ($c) use ($dic) {
            return $dic['rbacreview'];
        };

        $container['ilFavouritesManager'] = function ($c) use ($dic) {
            return new \ilFavouritesManager();
        };

        $container['plugin.id'] = function ($c) use ($plugin) {
            return $plugin->getId();
        };

        $container['plugin.txt'] = function ($c) use ($plugin) {
            return $plugin->txtClosure();
        };

        $container['plugin.path'] = function ($c) use ($plugin) {
            return $plugin->getDirectory();
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

        $container['user.autocomplete'] = function ($c) {
            return new ilUserAutoComplete();
        };

        $container['certificate.template.repository'] = function ($c) {
            return new ilCertificateTemplateRepository(
                $c['ilDB'],
                $c['ilLog']
            );
        };

        $container['certificate.type.class.map'] = function ($c) {
            return new ilCertificateTypeClassMap();
        };

        $container['certificate.queue.repository'] = function ($c) {
            return new ilCertificateQueueRepository(
                $c['ilDB'],
                $c['ilLog']
            );
        };

        $container['certificate.settings'] = function ($c) {
            return new ilSetting('certificate');
        };

        $container['certificate.cron'] = function ($c) {
            return new ilCertificateCron();
        };

        $container['data.change.helper'] = function ($c) {
            return new DataChangeHelper(
                $c['ilLog'],
                $c['user.autocomplete'],
                $c['certificate.template.repository'],
                $c['certificate.type.class.map'],
                $c['certificate.queue.repository'],
                $c['certificate.settings'],
                $c['certificate.cron']
            );
        };

        $container['security.db'] = function ($c) {
            return new ILIAS\ilDB(
                $c['ilDB']
            );
        };

        $container['update.user.certificate.gui.link'] = function ($c) use ($plugin) {
            require_once __DIR__ . '/Config/UpdateUserCertificate/class.ilUpdateUserCertificateGUI.php';
            return $c['ilCtrl']->getLinkTargetByClass(
                'ilUpdateUserCertificateGUI',
                \ilUpdateUserCertificateGUI::CMD_SHOW
            );
        };

        $container['update.user.certificate.gui'] = function ($c) use ($plugin) {
            require_once __DIR__ . '/Config/UpdateUserCertificate/class.ilUpdateUserCertificateGUI.php';
            return new \ilUpdateUserCertificateGUI(
                $c['ilCtrl'],
                $c['tpl'],
                $c['ilUser'],
                $c['plugin.txt'],
                $c['log.db'],
                $c['data.change.helper']
            );
        };

        $container['update.course.certificates.gui.link'] = function ($c) use ($plugin) {
            require_once __DIR__ . '/Config/UpdateCourseCertificates/class.ilUpdateCourseCertificatesGUI.php';
            return $c['ilCtrl']->getLinkTargetByClass(
                'ilUpdateCourseCertificatesGUI',
                \ilUpdateCourseCertificatesGUI::CMD_SHOW
            );
        };

        $container['update.course.certificates.gui'] = function ($c) use ($plugin) {
            require_once __DIR__ . '/Config/UpdateCourseCertificates/class.ilUpdateCourseCertificatesGUI.php';
            return new \ilUpdateCourseCertificatesGUI(
                $c['ilCtrl'],
                $c['tpl'],
                $c['ilUser'],
                $c['plugin.txt'],
                $c['log.db'],
                $c['data.change.helper']
            );
        };

        $container['remove.user.from.course..db'] = function ($c) {
            return new ilDB(
                $c['ilDB']
            );
        };

        $container['remove.user.from.course.gui.link'] = function ($c) use ($plugin) {
            require_once __DIR__ . '/Config/RemoveUserFromCourse/class.ilRemoveUserFromCourseGUI.php';
            return $c['ilCtrl']->getLinkTargetByClass(
                'ilRemoveUserFromCourseGUI',
                \ilRemoveUserFromCourseGUI::CMD_SHOW
            );
        };

        $container['remove.user.from.course.gui'] = function ($c) use ($plugin) {
            require_once __DIR__ . '/Config/RemoveUserFromCourse/class.ilRemoveUserFromCourseGUI.php';
            return new \ilRemoveUserFromCourseGUI(
                $c['ilCtrl'],
                $c['tpl'],
                $c['ilUser'],
                $c['rbacadmin'],
                $c['rbacreview'],
                $c['plugin.txt'],
                $c['remove.user.from.course..db'],
                $c['log.db'],
                $c['bwv.udf.db'],
                $c['data.change.helper'],
                $c['ilFavouritesManager']
            );
        };

        $container['merge.user.db'] = function ($c) {
            return new MergeUserDB(
                $c['ilDB']
            );
        };

        $container['merge.users.gui.link'] = function ($c) use ($plugin) {
            require_once __DIR__ . '/Config/MergeUsers/class.ilMergeUsersGUI.php';
            return $c['ilCtrl']->getLinkTargetByClass(
                'ilMergeUsersGUI',
                \ilMergeUsersGUI::CMD_SHOW
            );
        };

        $container['merge.users.gui'] = function ($c) use ($plugin) {
            require_once __DIR__ . '/Config/MergeUsers/class.ilMergeUsersGUI.php';
            return new \ilMergeUsersGUI(
                $c['ilCtrl'],
                $c['tpl'],
                $c['ilUser'],
                $c['plugin.txt'],
                $c['log.db'],
                $c['merge.user.db'],
                $c['data.change.helper']
            );
        };

        $container['remove.course.from.history.gui.link'] = function ($c) use ($plugin) {
            require_once __DIR__ . '/Config/RemoveCourseFromHistory/class.ilRemoveCourseFromHistoryGUI.php';
            return $c['ilCtrl']->getLinkTargetByClass(
                'ilRemoveCourseFromHistoryGUI',
                \ilRemoveCourseFromHistoryGUI::CMD_SHOW
            );
        };

        $container['remove.course.from.history.db'] = function ($c) {
            return new Config\RemoveCourseFromHistory\ilDB(
                $c['ilDB']
            );
        };

        $container['remove.course.from.history.gui'] = function ($c) use ($plugin) {
            require_once __DIR__ . '/Config/RemoveCourseFromHistory/class.ilRemoveCourseFromHistoryGUI.php';
            return new \ilRemoveCourseFromHistoryGUI(
                $c['ilCtrl'],
                $c['tpl'],
                $c['ilUser'],
                $c['plugin.txt'],
                $c['log.db'],
                $c['remove.course.from.history.db'],
                $c['data.change.helper']
            );
        };

        $container['reopen.course.member.webinar.gui.link'] = function ($c) use ($plugin) {
            require_once __DIR__ . '/Config/ReopenCourseMemberWebinar/class.ilReopenCourseMemberWebinarGUI.php';
            return $c['ilCtrl']->getLinkTargetByClass(
                'ilReopenCourseMemberWebinarGUI',
                \ilReopenCourseMemberWebinarGUI::CMD_SHOW
            );
        };

        $container['reopen.course.member.webinar.gui'] = function ($c) use ($plugin) {
            require_once __DIR__ . '/Config/ReopenCourseMemberWebinar/class.ilReopenCourseMemberWebinarGUI.php';
            return new \ilReopenCourseMemberWebinarGUI(
                $c['ilCtrl'],
                $c['tpl'],
                $c['ilUser'],
                $c['plugin.txt'],
                $c['log.db'],
                $c['data.change.helper']
            );
        };

        $container['bwv.udf.db'] = function ($c) {
            return new Config\UDF\ilDB(
                $c['ilDB'],
                $c['ilUser']
            );
        };

        $container['bwv.udf.gui.link'] = function ($c) {
            require_once __DIR__ . '/Config/UDF/class.ilBWVUDFGUI.php';
            return $c['ilCtrl']->getLinkTargetByClass(
                'ilBWVUDFGUI',
                \ilBWVUDFGUI::CMD_SHOW
            );
        };

        $container['bwv.udf.gui'] = function ($c) {
            require_once __DIR__ . '/Config/UDF/class.ilBWVUDFGUI.php';
            return new \ilBWVUDFGUI(
                $c['ilCtrl'],
                $c['tpl'],
                $c['bwv.udf.db'],
                $c['plugin.txt']
            );
        };

        $container['security.gui.link'] = function ($c) use ($plugin) {
            require_once __DIR__ . '/Config/Security/class.ilDCSecurityGUI.php';
            return $c['ilCtrl']->getLinkTargetByClass(
                'ilDCSecurityGUI',
                \ilDCSecurityGUI::CMD_SHOW_CONFIG
            );
        };

        $container['security.gui'] = function ($c) use ($plugin) {
            require_once __DIR__ . '/Config/Security/class.ilDCSecurityGUI.php';
            return new \ilDCSecurityGUI(
                $c['ilCtrl'],
                $c['tpl'],
                $c['plugin.txt'],
                $c['plugin.id'],
                $c['security.db']
            );
        };

        $container['log.db'] = function ($c) {
            return new LogDB(
                $c['ilDB']
            );
        };

        $container['log.gui.link'] = function ($c) {
            require_once __DIR__ . '/Config/Log/class.ilDCLogGUI.php';
            return $c['ilCtrl']->getLinkTargetByClass(
                'ilDCLogGUI',
                \ilDCLogGUI::CMD_SHOW_LOG
            );
        };

        $container['log.gui'] = function ($c) {
            require_once __DIR__ . '/Config/Log/class.ilDCLogGUI.php';
            return new \ilDCLogGUI(
                $c['ilCtrl'],
                $c['tpl'],
                $c['log.db'],
                $c['plugin.txt'],
                $c['plugin.path']
            );
        };

        return $container;
    }
}
