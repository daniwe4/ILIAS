<?php

namespace CaT\Plugins\UserCourseHistorizing;

use Pimple\Container;

use CaT\Historization as Hist;
use CaT\Historization\HistCase\HistCase;
use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistCourse;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistUserCourse;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistSessionCourse;
use CaT\Plugins\UserCourseHistorizing\HistorizingFromBufferJob;

trait DI
{
    protected function buildDIC(\ArrayAccess $dic)
    {
        $c = new Container();

        $c["CONDENSATION_TIME_IN_S"] = 60;

        $c["ilDB"] = function ($c) use ($dic) {
            return $dic["ilDB"];
        };

        $c["job"] = function ($c) {
            return new HistorizingFromBufferJob(
                $c["buffer_storage"],
                ...$c["cases"]
            );
        };

        $c["case.course"] = function ($c) {
            return new HistCourse();
        };
        $c["case.user_course"] = function ($c) {
            return new HistUserCourse();
        };
        $c["case.session_course"] = function ($c) {
            return new HistSessionCourse();
        };
        $c["cases"] = function ($c) {
            return array(
                $c["case.course"],
                $c["case.user_course"],
                $c["case.session_course"]
            );
        };

        $c["ilias.sql"] = function ($c) {
            return new ILIAS\IliasSql($c["ilDB"]);
        };

        $c["buffer.factory"] = function ($c) {
            return new Mysql\MysqlBufferFactory($c["ilias.sql"]);
        };
        $c["buffer.access"] = function ($c) {
            return new Hist\BufferAccess($c["buffer.factory"]);
        };

        $c["storage.factory"] = function ($c) {
            return new Mysql\MysqlStorageFactory($c["ilias.sql"]);
        };

        $c["buffer_storage"] = function ($c) {
            return new Hist\BufferStorageInterface(
                $c["buffer.factory"],
                $c["storage.factory"],
                $c["condenser.frequentUpdates"]
            );
        };
        $c["buffer_storage.user_course"] = function ($c) {
            return $c["buffer_storage"]->withHistCase($c["case.user_course"]);
        };

        $c["condenser.frequentUpdates"] = function ($c) {
            return new FrequentUpdatesCondenserWithFieldsNotToCondense(
                $c["CONDENSATION_TIME_IN_S"]
            );
        };

        return $c;
    }
}
