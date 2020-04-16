<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WorkflowReminder;

use Pimple\Container;

trait DI
{
    public function getPluginDIC(
        \ilWorkflowReminderPlugin $plugin,
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
        $container["tpl"] = function ($c) use ($dic) {
            return $dic["tpl"];
        };
        $container["ilAppEventHandler"] = function ($c) use ($dic) {
            return $dic["ilAppEventHandler"];
        };
        $container["plugin.path"] = function ($c) use ($plugin) {
            return $plugin->getDirectory();
        };
        $container["txtclosure"] = function ($c) use ($plugin) {
            return function ($code) use ($plugin) {
                return $plugin->txt($code);
            };
        };

        $container["jobs.get"] = function ($c) {
            return function ($job_id) use ($c) {
                switch ($job_id) {
                    case MinMember\MinMemberJob::ID:
                        return $c["jobs.minmember"];
                    case NotFinalized\CourseMember\NotFinalizedJob::ID:
                        return $c["jobs.notfinalized.coursemember"];
                    case NotFinalized\Webinar\NotFinalizedJob::ID:
                        return $c["jobs.notfinalized.webinar"];
                }
            };
        };

        $container["jobs.getall"] = function ($c) {
            return [
                $c["jobs.minmember"],
                $c["jobs.notfinalized.coursemember"],
                $c["jobs.notfinalized.webinar"]
            ];
        };

        $container["db.minmember"] = function ($c) {
            return new MinMember\ilDB($c["ilDB"]);
        };

        $container["jobs.minmember"] = function ($c) {
            return new MinMember\MinMemberJob(
                $c["db.minmember"],
                $c["ilAppEventHandler"],
                $c["txtclosure"]
            );
        };

        $container["db.notfinalized.log"] = function ($c) {
            return new NotFinalized\Log\ilDB($c["ilDB"]);
        };

        $container["db.notfinalized.coursemember"] = function ($c) {
            return new NotFinalized\CourseMember\ilDB($c["ilDB"]);
        };

        $container["jobs.notfinalized.coursemember"] = function ($c) {
            return new NotFinalized\CourseMember\NotFinalizedJob(
                $c["db.notfinalized.coursemember"],
                $c["db.notfinalized.log"],
                $c["ilAppEventHandler"],
                $c["txtclosure"]
            );
        };

        $container["db.notfinalized.webinar"] = function ($c) {
            return new NotFinalized\Webinar\ilDB($c["ilDB"]);
        };

        $container["jobs.notfinalized.webinar"] = function ($c) {
            return new NotFinalized\Webinar\NotFinalizedJob(
                $c["db.notfinalized.webinar"],
                $c["db.notfinalized.log"],
                $c["ilAppEventHandler"],
                $c["txtclosure"]
            );
        };

        return $container;
    }
}
