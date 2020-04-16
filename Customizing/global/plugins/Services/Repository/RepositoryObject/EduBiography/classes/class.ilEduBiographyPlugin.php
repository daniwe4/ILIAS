<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once 'Services/Repository/classes/class.ilRepositoryObjectPlugin.php';

use CaT\Plugins\EduBiography\Settings;

class ilEduBiographyPlugin extends ilRepositoryObjectPlugin
{
    const CERTIFICATE_PATH = "/Plugin/EduBiography/Certificates/";

    public function __construct()
    {
        parent::__construct();
        global $DIC;
        $this->lng = $DIC['lng'];
    }

    const COPY_OPERATION_ID = 58;

    public function getPluginName()
    {
        return 'EduBiography';
    }

    public function uninstallCustom()
    {
        global $DIC;
        $db = $DIC['ilDB'];
        if ($db->tableExists(Settings\SettingsRepository::DB_TABLE)) {
            $db->dropTable(Settings\SettingsRepository::DB_TABLE);
        }
    }

    /**
     * @inheritdoc
     */
    protected function beforeActivation()
    {
        parent::beforeActivation();
        global $DIC;
        $db = $DIC->database();

        $type = $this->getId();

        if (!$this->isRepositoryPlugin($type)) {
            throw new ilPluginException("Object plugin type must start with an x. Current type is " . $type . ".");
        }

        $type_id = $this->getTypeId($type, $db);
        if (!$type_id) {
            $type_id = $this->createTypeId($type, $db);
        }

        $this->assignCopyPermissionToPlugin($type_id, $db);
        $this->createPluginPermissions($type_id, $db);

        return true;
    }

    protected function createPluginPermissions(int $type_id, \ilDBInterface $db)
    {
        include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
        $new_rbac_options = [
            ["download_overview_certificate", "User is able to download the overview certificate", "object", 2700]
        ];

        foreach ($new_rbac_options as $value) {
            if (!$this->permissionExists($value[0], $db)) {
                $new_ops_id = \ilDBUpdateNewObjectType::addCustomRBACOperation($value[0], $value[1], $value[2], $value[3]);
                \ilDBUpdateNewObjectType::addRBACOperation($type_id, $new_ops_id);
            }
        }
    }

    /**
     * Check current plugin is repository plugin
     *
     * @param string 	$type
     *
     * @return bool
     */
    protected function isRepositoryPlugin($type)
    {
        return substr($type, 0, 1) == "x";
    }

    /**
     * Get id of current type
     *
     * @param string 	$type
     * @param 			$db
     *
     * @return int | null
     */
    protected function getTypeId($type, $db)
    {
        $set = $db->query("SELECT obj_id FROM object_data\n" .
            " WHERE type = " . $db->quote("typ", "text") . "\n" .
            " AND title = " . $db->quote($type, "text"));

        if ($db->numRows($set) == 0) {
            return null;
        }

        $rec = $db->fetchAssoc($set);
        return (int) $rec["obj_id"];
    }

    /**
     * Create a new entry in object data
     *
     * @param string 	$type
     * @param 			$db
     *
     * @return int
     */
    protected function createTypeId($type, \ilDBInterface $db)
    {
        assert('is_string($type)');

        $type_id = $db->nextId("object_data");
        $db->manipulate("INSERT INTO object_data\n" .
            "(obj_id, type, title, description, owner, create_date, last_update) VALUES (\n" .
            $db->quote($type_id, "integer") . ",\n" .
            $db->quote("typ", "text") . ",\n" .
            $db->quote($type, "text") . ",\n" .
            $db->quote("Plugin " . $this->getPluginName(), "text") . ",\n" .
            $db->quote(-1, "integer") . ",\n" .
            $db->quote(ilUtil::now(), "timestamp") . ",\n" .
            $db->quote(ilUtil::now(), "timestamp") .
            ")");

        return $type_id;
    }

    /**
     * Assign permission copy to current plugin
     *
     * @param int 		$type_id
     * @param \ilDBInterface 	$db
     *
     * @return int
     */
    protected function assignCopyPermissionToPlugin($type_id, \ilDBInterface $db)
    {
        assert('is_int($type_id)');
        $ops = array(self::COPY_OPERATION_ID);

        foreach ($ops as $op) {
            // check whether type exists in object data, if not, create the type

            if (!$this->permissionIsAssigned($type_id, $op, $db)) {
                $db->manipulate("INSERT INTO rbac_ta " .
                    "(typ_id, ops_id) VALUES (" .
                    $db->quote($type_id, "integer") . "," .
                    $db->quote($op, "integer") .
                    ")");
            }
        }
    }

    /**
     * Check the permission is already created
     *
     * @param string 	$permission
     * @param \ilDBInterface	$db
     *
     * @return bool
     */
    protected function permissionExists($permission, \ilDBInterface $db)
    {
        assert('is_string($permission)');

        $query = "SELECT count(ops_id) AS cnt FROM rbac_operations\n"
                . " WHERE operation = " . $db->quote($permission, 'text');

        $res = $db->query($query);
        $row = $db->fetchAssoc($res);

        return $row["cnt"] > 0;
    }

    /**
     * Checks permission is not assigned to plugin
     *
     * @param int 		$type_id
     * @param int 		$op_id
     * @param \ilDBInterface	$db
     *
     * @return bool
     */
    protected function permissionIsAssigned($type_id, $op_id, \ilDBInterface $db)
    {
        assert('is_int($type_id)');
        assert('is_int($op_id)');
        $set = $db->query("SELECT count(typ_id) as cnt FROM rbac_ta " .
                " WHERE typ_id = " . $db->quote($type_id, "integer") .
                " AND ops_id = " . $db->quote($op_id, "integer"));

        $rec = $db->fetchAssoc($set);

        return $rec["cnt"] > 0;
    }

    /**
     * decides if this repository plugin can be copied
     *
     * @return bool
     */
    public function allowCopy()
    {
        return true;
    }

    /**
     * Get path of existing certificate
     *
     * @param int 	$crs_id
     * @param int 	$cert_user_id
     *
     * @return string | null
     */
    public function getCertifcatePath($crs_id, $cert_user_id, $filename)
    {
        $file_storage = new ilCertificateStorage($crs_id, $cert_user_id);
        return $file_storage->getPathOfCurrentCertificate($filename);
    }

    /**
     * Saves current certicate data into file
     *
     * @param string 	$certificate_content
     * @param string 	$filename
     *
     * @return sring
     */
    public function saveCertificate($crs_id, $cert_user_id, $certificate_content, $filename)
    {
        $file_storage = new ilCertificateStorage($crs_id, $cert_user_id);
        return $file_storage->saveCertificate($certificate_content, $filename);
    }

    /**
     * Get the file name of certificate
     *
     * @param ilObjCourse 	$crs
     * @param int 	$cert_user_id
     *
     * @return string
     */
    public function getCertifacteFileName()
    {
        return $this->txt("certifacte_name") . ".pdf";
    }

    /**
     * Checks the course is deleted
     *
     * @param int 	$crs_id
     *
     * @return bool
     */
    public function crsDeleted($crs_id)
    {
        $ref_id = array_shift(\ilObject::_getAllReferences($crs_id));
        if (is_null($ref_id)) {
            return true;
        }

        return \ilObject::_lookupDeletedDate($ref_id) !== null;
    }

    /**
     * Get a closure to get txts from plugin.
     *
     * @return \Closure
     */
    public function txtClosure()
    {
        return function ($code) {
            return $this->txt($code);
        };
    }

    public function getDeleteTools()
    {
        global $DIC;
        $db = new TroubleShooting\ilDB($DIC["ilDB"], $DIC["ilUser"]);
        return new TroubleShooting\DeleteCertificates($db);
    }



    public function getAllVisibleFields() : array
    {
        return $this->getBasicVisibleOptions()
            + $this->getAllVisibleUDFFields()
            + $this->getCourseVisibleOptions();
    }

    /**
     * @return string[]
     */
    public function getAllVisibleUDFFields() : array
    {
        $il_udf = \ilUserDefinedFields::_getInstance();
        $ret = [];
        foreach ($il_udf->getCourseExportableFields() as $udf_def) {
            $ret["UDF_" . $udf_def['field_id']] = $udf_def['field_name'];
        }
        return $ret;
    }
    protected function getBasicVisibleOptions() : array
    {
        return [
            "orgus" => $this->txt("orgus"),
            "sum_idd_achieved" => $this->txt("sum_idd_achieved"),
            "sum_idd_forecast" => $this->txt("sum_idd_forecast"),
            "count_booked" => $this->txt("cnt_booked"),
            "count_waiting" => $this->txt("cnt_waiting"),
            "count_participated" => $this->txt("cnt_participated"),
            "count_absent" => $this->txt("cnt_absent"),
            "cnt_cancelled_after_dl" => $this->txt("cnt_cancelled_after_dl")
        ];
    }

    protected function getCourseVisibleOptions() : array
    {
        $udf_fields = array_filter(
            $this->getAllCourseVisibleStandardUserFields(),
            function ($column) {
                if (
                in_array(
                    $column,
                    [
                        "firstname",
                        "lastname",
                        "username"
                    ]
                )
                ) {
                    return false;
                }
                return true;
            }
        );
        $udf_fields = array_combine($udf_fields, $udf_fields);
        $ret = [];
        foreach ($udf_fields as $col => $title) {
            $ret["UDF_" . $col] = $this->lng->txt($title);
        }
        return $ret;
    }

    /**
     * @return string[]
     */
    public function getAllCourseVisibleStandardUserFields() : array
    {
        $ef = \ilExportFieldsInfo::_getInstanceByType("crs");
        return $ef->getExportableFields();
    }
}
