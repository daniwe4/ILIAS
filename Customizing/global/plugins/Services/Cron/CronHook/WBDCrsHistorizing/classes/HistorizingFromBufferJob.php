<?php

namespace CaT\Plugins\WBDCrsHistorizing;

use CaT\Historization as Hist;
use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 *
 * @author Denis Klöpfer
 * @version $Id$
 */

require_once "Services/Cron/classes/class.ilCronManager.php";
require_once "Services/Cron/classes/class.ilCronJob.php";
require_once "Services/Cron/classes/class.ilCronJobResult.php";

class HistorizingFromBufferJob extends \ilCronJob
{
    private $db;
    private $cases;

    public function __construct($ilDB, array $cases)
    {
        $this->db = $ilDB;
        $this->cases = $cases;
    }

    /**
     * Implementation of abstract function from ilCronJob
     * @return	string
     */
    public function getId()
    {
        return 'hist_wbdcrs_from_buffer';
    }

    /**
     * Implementation of abstract function from ilCronJob
     * @return	string
     */
    public function getTitle()
    {
        return 'Read buffer and historize content of WBD history.';
    }

    /**
     * Implementation of abstract function from ilCronJob
     * @return	bool
     */
    public function hasAutoActivation()
    {
        return true;
    }

    /**
     * Implementation of abstract function from ilCronJob
     * @return	bool
     */
    public function hasFlexibleSchedule()
    {
        return true;
    }

    /**
     * Implementation of abstract function from ilCronJob
     * @return	int
     */
    public function getDefaultScheduleType()
    {
        return \ilCronJob::SCHEDULE_TYPE_IN_MINUTES;
    }

    /**
     * Implementation of abstract function from ilCronJob
     * @return	int
     */
    public function getDefaultScheduleValue()
    {
        return 10;
    }

    /**
     * Implementation of abstract function from ilCronJob
     * @return	ilCronJobResult
     */
    public function run()
    {
        global $DIC;
        $sql = new ILIAS\IliasSql($DIC['ilDB']);
        $i = new Hist\BufferStorageInterface(new Mysql\MysqlBufferFactory($sql), new Mysql\MysqlStorageFactory($sql), new Hist\Condenser\FrequentUpdates\FrequentUpdatesCondenser(60));
        foreach ($this->cases as $case) {
            $i->withHistCase($case)->transfer(
                function () {
                    \ilCronManager::ping($this->getId());
                }
            );
        }
        $cron_result = new \ilCronJobResult();
        \ilCronManager::ping($this->getId());
        $cron_result->setStatus(\ilCronJobResult::STATUS_OK);
        return $cron_result;
    }
}
