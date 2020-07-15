<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\CourseCreation;

/**
 * Creates courses based on templates.
 */
class Process
{
    const WAIT_FOR_OBJ_CLONED_CHECK_IN_S = 5;
    const MAX_CLONE_WAITING_TIME_BEVORE_CANCEL_IN_S = 600;
    const SOAP_TIMEOUT_IN_S = 30;
    const EDU_TRACKING = "xetr";
    const COURSE_CLASSIFICATION = "xccl";
    const SESSION = "sess";

    private static $RUN_AT_LAST = array(
        self::COURSE_CLASSIFICATION,
        self::SESSION,
        self::EDU_TRACKING
    );

    /**
     * @var	\ilTree
     */
    protected $tree;

    /**
     * @var	\ilDBInterface
     */
    protected $db;

    /**
     * @var \ilObjectDefinition
     */
    protected $objdefinition;

    public function __construct(
        \ilTree $tree,
        \ilDBInterface $db,
        \ilObjectDefinition $objdefinition
    ) {
        $this->tree = $tree;
        $this->db = $db;
        $this->objdefinition = $objdefinition;
    }

    /**
     * Run the course creation process for a given course.
     *
     * @return Request
     */
    public function run(Request $request)
    {
        $ref_id = $this->cloneAllObject($request);

        $request = $request->withTargetRefIdAndFinishedTS((int) $ref_id, new \DateTime());

        $this->adjustCourseTitle($request);
        $this->setCourseOnline($request);
        $this->configureCopiedObjects($request);
        $this->setOwner($request);
        $this->assignCreatorRole($request);
        $this->getObjectByRefId($ref_id)->update();
        \ilSession::_destroy($request->getSessionId());

        return $request;
    }

    /**
     * Get copy options for the ilCopyWizard from the request.
     *
     * @param Request	$request
     * @return	array
     */
    protected function getCopyWizardOptions(Request $request)
    {
        $sub_nodes = $this->tree->getSubTreeIds($request->getCourseRefId());
        $options = [];
        foreach ($sub_nodes as $sub) {
            $options[(int) $sub] = ["type" => $request->getCopyOptionFor((int) $sub)];
        }
        return $options;
    }

    /**
     * Remove the residues from the copy process in the title.
     *
     * @param	Request		$request
     * @return void
     */
    protected function adjustCourseTitle($request)
    {
        $crs_ref_id = $request->getTargetRefId();
        $crs = $this->getObjectByRefId($crs_ref_id);
        $title = $crs->getTitle();
        $matches = [];
        preg_match("/^(.*)\s-\s.*$/", $title, $matches);
        $crs->setTitle($matches[1]);
        $crs->update();
    }

    /**
     * Set course online.
     *
     * @param	Request		$request
     * @return void
     */
    protected function setCourseOnline($request)
    {
        $crs_ref_id = $request->getTargetRefId();
        $crs = $this->getObjectByRefId($crs_ref_id);
        $crs->setOfflineStatus(false);
        $crs->update();
    }

    /**
     * Configure copied objects.
     *
     * @param	Request $request
     * @return	null
     */
    protected function configureCopiedObjects(Request $request)
    {
        $target_ref_id = $request->getTargetRefId();
        assert('!is_null($target_ref_id)');

        $sub_nodes = array_merge(
            [$target_ref_id],
            $this->tree->getSubTreeIds($target_ref_id)
        );
        $last = array();
        $mappings = $this->getCopyMappings($sub_nodes);

        foreach ($sub_nodes as $sub) {
            $configs = $request->getConfigurationFor($mappings[$sub]);
            if (array_key_exists($sub, $mappings)) {
                $this->setOwnerToSubObjects((int) $sub, (int) $mappings[$sub]);
            }

            if ($configs === null) {
                continue;
            }
            $object = $this->getObjectByRefId((int) $sub);
            assert('method_exists($object, "afterCourseCreation")');

            if (in_array($object->getType(), self::$RUN_AT_LAST)) {
                $last[$object->getType()][] = array("object" => $object, "configs" => $configs);
                continue;
            }

            foreach ($configs as $config) {
                $object->afterCourseCreation($config);
            }
        }
        foreach (self::$RUN_AT_LAST as $type) {
            if (array_key_exists($type, $last)) {
                foreach ($last[$type] as $obj) {
                    foreach ($obj['configs'] as $config) {
                        $obj['object']->afterCourseCreation($config);
                    }
                }
            }
        }
    }

    /**
     * Make sure the owner of template is owner of created course
     *
     * @param Request 	$request
     *
     * @return void
     */
    protected function setOwner(Request $request)
    {
        $target_crs_ref_id = $request->getTargetRefId();
        $target_crs = $this->getObjectByRefId($target_crs_ref_id);
        $target_crs_obj_id = $target_crs->getId();

        $tpl_crs_ref_id = $request->getCourseRefId();
        $tpl_crs = $this->getObjectByRefId($tpl_crs_ref_id);

        $tpl_owner = $tpl_crs->getOwner();

        // ilObject::update() doesn't change the owner of an object
        $where = array("obj_id" => array("integer", $target_crs_obj_id));
        $values = array("owner" => array("integer", $tpl_owner));

        $this->db->update("object_data", $values, $where);
    }

    /**
     * Make sure the cloned children get the same owner as in template
     *
     * @param int 	$trgt_id
     * @param int 	$src_id
     *
     * @return void
     */
    protected function setOwnerToSubObjects($trgt_id, $src_id)
    {
        assert('is_int($trgt_id)');
        assert('is_int($src_id)');
        $target = $this->getObjectByRefId($trgt_id);
        $source = $this->getObjectByRefId($src_id);

        $source_owner = $source->getOwner();

        $where = array("obj_id" => array("integer", $target->getId()));
        $values = array("owner" => array("integer", $source_owner));

        $this->db->update("object_data", $values, $where);
    }

    /**
     * Assigns creator to specified role
     *
     * @param Request 	$request
     *
     * @return void
     */
    protected function assignCreatorRole(Request $request)
    {
        $user_id = $request->getUserId();
        $crs_ref_id = $request->getCourseRefId();
        $target_crs_ref_id = $request->getTargetRefId();

        $xcps = $this->getFirstChildOfByType($crs_ref_id, "xcps");

        if (!is_null($xcps)) {
            $xcps->workLocalRolesForUsers($user_id, $target_crs_ref_id);
        }
    }

    /**
     * Get first child by type recursive
     *
     * @param int 	$ref_id
     * @param string 	$search_type
     *
     * TODO: dedup with ilCourseTemplateDB and BookingModalitiesDigester in
     *       UserCourseHistorizing
     *
     * @return Object 	of search type
     */
    protected function getFirstChildOfByType($ref_id, $search_type)
    {
        $childs = $this->tree->getChilds($ref_id);

        foreach ($childs as $child) {
            $type = $child["type"];
            if ($type == $search_type) {
                return \ilObjectFactory::getInstanceByRefId($child["child"]);
            }

            if ($this->objdefinition->isContainer($type)) {
                $ret = $this->getFirstChildOfByType($child["child"], $search_type);
                if (!is_null($ret)) {
                    return $ret;
                }
            }
        }

        return null;
    }

    /**
     * Get copy mappings for ref_ids, where target => source.
     *
     * @param	int[]	$ref_ids
     * @return	array<int,int>
     */
    protected function getCopyMappings(array $ref_ids)
    {
        $res = $this->db->query(
            "SELECT tgt.ref_id tgt_ref, src.ref_id src_ref " .
            "FROM object_reference tgt " .
            "JOIN copy_mappings mp ON tgt.obj_id = mp.obj_id " .
            "JOIN object_reference src ON mp.source_id = src.obj_id " .
            "WHERE " . $this->db->in("tgt.ref_id", $ref_ids, false, "integer")
        );
        $mappings = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $mappings[(int) $row["tgt_ref"]] = (int) $row["src_ref"];
        }
        return $mappings;
    }

    /**
     * Get an object for the given ref.
     *
     * @param	int		$ref_id
     * @return	\ilObject
     */
    protected function getObjectByRefId($ref_id)
    {
        assert('is_int($ref_id)');
        $object = \ilObjectFactory::getInstanceByRefId($ref_id);
        assert('$object instanceof \ilObject');
        return $object;
    }

    /**
     * Our custom version of ilContainer::cloneAllObject.
     *
     * Allows us to mess with modalities of creation via SOAP.
     *
     * @param	Request $request
     * @return	int ref_id of clone
     */
    protected function cloneAllObject(Request $request)
    {
        global $ilLog, $ilAccess,$ilErr,$rbacsystem;

        include_once('./Services/Link/classes/class.ilLink.php');
        include_once('Services/CopyWizard/classes/class.ilCopyWizardOptions.php');

        $session_id = $request->getSessionId();
        $client_id = CLIENT_ID;
        $new_type = "crs";

        $clone_source = $request->getCourseRefId();
        $ref_id = $request->getNewParentRefId();
        if (is_null($ref_id) || $ref_id == "" || $ref_id == 0) {
            $ref_id = $this->getTemplateParentRefId($clone_source);
        }

        $options = $this->getCopyWizardOptions($request);
        $a_submode = 1;

        // Save wizard options
        $copy_id = \ilCopyWizardOptions::_allocateCopyId();
        $wizard_options = \ilCopyWizardOptions::_getInstance($copy_id);
        $wizard_options->saveOwner($request->getUserId());
        $wizard_options->saveRoot($clone_source);

        // add entry for source container
        $wizard_options->initContainer($clone_source, $ref_id);

        foreach ($options as $source_id => $option) {
            $wizard_options->addEntry($source_id, $option);
        }
        $wizard_options->read();
        $wizard_options->storeTree($clone_source);

        // Duplicate session to avoid logout problems with backgrounded SOAP calls
        // Start cloning process using soap call
        include_once 'Services/WebServices/SOAP/classes/class.ilSoapClient.php';

        $soap_client = new \ilSoapClient();
        $soap_client->setResponseTimeout(self::SOAP_TIMEOUT_IN_S);
        $soap_client->enableWSDL(true);

        $ilLog->write(__METHOD__ . ': Trying to call Soap client...');
        if (!$soap_client->init()) {
            throw new \RuntimeException("Could not init SOAP client.");
        }

        \ilLoggerFactory::getLogger('obj')->info('Calling soap clone method');
        $res = $soap_client->call('ilClone', array($session_id . '::' . $client_id, $copy_id));

        if ($res === false || !is_numeric($res)) {
            throw new \RuntimeException("Could not clone course via SOAP.");
        }

        $this->waitForCloneFinished((int) $copy_id);

        return (int) $res;
    }

    /**
     * Get parent ref id of template
     *
     * @param int 	$tpl_ref_id
     *
     * @return int
     */
    protected function getTemplateParentRefId($tpl_ref_id)
    {
        return $this->tree->getParentId($tpl_ref_id);
    }



    /**
     * Checks the copy wizard has totaly finished
     *
     * @param int 	$copy_id
     * @throws Exception 	If cloning passed a specific timespan
     *
     * @return bool
     */
    protected function waitForCloneFinished($copy_id)
    {
        assert('is_int($copy_id)');
        $time = time();

        while (!\ilCopyWizardOptions::_isFinished($copy_id)) {
            if (time() >= $time + self::MAX_CLONE_WAITING_TIME_BEVORE_CANCEL_IN_S) {
                throw new \Exception(
                    "Max duration time for cloning is passed: "
                    . self::MAX_CLONE_WAITING_TIME_BEVORE_CANCEL_IN_S
                    . " seconds."
                );
            }

            sleep(self::WAIT_FOR_OBJ_CLONED_CHECK_IN_S);
        }

        return true;
    }
}
