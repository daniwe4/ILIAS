<?php
namespace CaT\Plugins\CronJobSurveillance\Config;

/**
 * Database queries against table ccjs_job_settings.
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class ilDB implements DB
{
    const TABLENAME = "ccjs_job_settings";
    const TABLENAME_HIST = "ccjs_job_settings_hist";

    /**
     * @var ilDBInterface
     */
    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        global $DIC;
        $this->g_usr = $DIC->user();
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function select() : array
    {
        $ret = array();

        $query =
             "SELECT job_id, tolerance" . PHP_EOL
            . "FROM " . self::TABLENAME . PHP_EOL
            . "ORDER BY id ASC" . PHP_EOL
        ;

        $result = $this->db->query($query);

        while ($row = $this->db->fetchAssoc($result)) {
            $ret[] = $row;
        }

        return $this->generateJobSettingObjects($ret);
    }

    /**
     * @inheritdoc
     */
    public function create(JobSetting $job_setting, int $counter) : void
    {
        $values = array(
            'job_id' => array('text', $job_setting->getJobId()),
            'tolerance' => array('integer', $job_setting->getTolerance()),
            'id' => array('integer', $counter)
        );

        $this->db->insert(self::TABLENAME, $values);

        $this->createHist($job_setting);
    }

    /**
     * Save each new entry also to a hist table.
     *
     * @param 	JobSetting 	$job_setting
     * @return 	void
     */
    protected function createHist(JobSetting $job_setting) : void
    {
        $next_id = (int) $this->db->nextId(self::TABLENAME_HIST);

        $values = array(
            'hist_id' => array('integer', $next_id),
            'job_id' => array('text', $job_setting->getJobId()),
            'tolerance' => array('integer', $job_setting->getTolerance()),
            'last_change' => array('text', date('Y-m-d H:i:s')),
            'changed_by' => array('integer', $this->g_usr->getId())
        );

        $this->db->insert(self::TABLENAME_HIST, $values);
    }

    /**
     * @inheritdoc
     */
    public function selectForJob(string $job_id) : array
    {
        $query =
             "SELECT job_id, tolerance" . PHP_EOL
            . "FROM " . self::TABLENAME . PHP_EOL
            . "WHERE job_id = " . $this->db->quote($job_id, "integer") . PHP_EOL
        ;

        $result = $this->db->query($query);

        if ($this->db->getNumRows($result) > 1) {
            throw new \Exception("To much entries found for job id " . $job_id);
        }

        $row = $this->db->fetchAssoc($result);

        return $this->generateJobSettingObjects($row);
    }

    /**
     * @inheritdoc
     */
    public function deleteForJob(string $job_id) : void
    {
        $query =
             "DELETE FROM " . self::TABLENAME . PHP_EOL
            . "WHERE job_id = " . $this->db->quote($job_id, "text") . PHP_EOL
        ;

        $this->db->manipulate($query);
    }

    /**
     * Delete all entries.
     */
    public function deleteAll() : void
    {
        $query = "DELETE FROM " . self::TABLENAME;
        $this->db->manipulate($query);
    }

    /**
     * Get all JobSetting objects from db.
     *
     * @return array
     */
    protected function generateJobSettingObjects(array $rows) : array
    {
        $job_setting_objects = array_map(function ($row) {
            return new JobSetting($row[ConfigurationForm::F_JOB_ID], (int) $row[ConfigurationForm::F_TOLERANCE]);
        }, $rows);

        return $job_setting_objects;
    }

    /**
     * Create table ccjs_job_settings.
     *
     * @return void
     */
    public function createTable() : void
    {
        if (!$this->db->tableExists(self::TABLENAME)) {
            $fields = array(
                'job_id' => array(
                    'type' => 'text',
                    'length' => 50,
                    'notnull' => true
                ),
                'tolerance' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                )
            );

            $this->db->createTable(self::TABLENAME, $fields);
        }
    }

    /**
     * Set the primary key for table ccjs_job_settings-
     *
     * @return void
     */
    public function createPrimaryKey() : void
    {
        $this->db->addPrimaryKey(self::TABLENAME, array("job_id"));
    }

    /**
     * Create table ccjs_job_settings_hist.
     *
     * @return void
     */
    public function createHistTable() : void
    {
        if (!$this->db->tableExists(self::TABLENAME_HIST)) {
            $fields = array(
                'hist_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                'job_id' => array(
                    'type' => 'text',
                    'length' => 50,
                    'notnull' => true
                ),
                'tolerance' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                'last_change' => array(
                    'type' => 'text',
                    'length' => 20,
                    'notnull' => true
                ),
                'changed_by' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                )
            );

            $this->db->createTable(self::TABLENAME_HIST, $fields);
        }
    }

    /**
     * Set the primary key for table ccjs_job_settings_hist.
     *
     * @return void
     */
    public function createHistPrimaryKey() : void
    {
        $this->db->addPrimaryKey(self::TABLENAME_HIST, array("hist_id"));
    }

    public function createHistSequence() : void
    {
        $this->db->createSequence(self::TABLENAME_HIST);
    }

    public function update1() : void
    {
        if (!$this->db->tableColumnExists(self::TABLENAME, "id")) {
            $field = array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    );

            $this->db->addTableColumn(self::TABLENAME, "id", $field);
        }
    }
}
