<?php

namespace CaT\Plugins\BookingModalities;

use CaT\Plugins\BookingModalities\Settings\SelectableReasons\SelectableReason;

/**
 * Communication class between front- and backend.
 * E.g. GUI only use this class to get information from ILIAS DB.
 */
class ilActions
{
    /**
     * @var \ilBookingModalitiesPlugin
     */
    protected $plugin;

    /**
     * @var Settings\SelectableRoles\DB
     */
    protected $selectable_roles_db;

    /**
     * @var Settings\SelectableReasons\DB
     */
    protected $selectable_reasons_db;

    /**
     * @var Settings\DownloadableDocument\FileStorage
     */
    protected $document_file_storage;

    /**
     * @var Settings\DownloadableDocument\DB
     */
    protected $document_db;


    const MODALITIES_DOC_NO_FILE = "NO_FILE";


    public function __construct(
        \ilBookingModalitiesPlugin $plugin,
        Settings\SelectableRoles\DB $selectable_roles_db,
        Settings\SelectableReasons\DB $selectable_reasons_db,
        Settings\DownloadableDocument\FileStorage $document_file_storage,
        Settings\DownloadableDocument\DB $document_db
    ) {
        $this->plugin = $plugin;
        $this->selectable_roles_db = $selectable_roles_db;
        $this->selectable_reasons_db = $selectable_reasons_db;
        $this->document_file_storage = $document_file_storage;
        $this->document_db = $document_db;
    }

    /**
     * Get the plugin instanz
     *
     * @return \ilBookingModalitiesPlugin
     */
    public function getPlugin()
    {
        if ($this->plugin === null) {
            throw new \LogicException("No plugin was set");
        }
        return $this->plugin;
    }

    /**
     * Get options of roles for multiselect input
     *
     * @return string[]
     */
    public function getRoleOptions()
    {
        return $this->selectable_roles_db->getRoleOptions();
    }

    /**
     * Get all plugin assigned roles
     *
     * @return string[]
     */
    public function getAssignedRoles()
    {
        return $this->selectable_roles_db->select();
    }

    /**
     * Save selected roles
     *
     * @param  int	$roles
     *
     * @return null
     */
    public function saveRoles(array $roles)
    {
        $this->selectable_roles_db->unassignRoles();
        $this->selectable_roles_db->assignRoles($roles);
    }

    /**
     * Get all selectable reasons
     *
     * @return SelectableReasons[]
     */
    public function getSelectableReasons()
    {
        return $this->selectable_reasons_db->select();
    }

    /**
     * Get new emty selectable reason
     *
     * @return SelectableReason
     */
    public function getNewSelectableReason()
    {
        return $this->selectable_reasons_db->newSelectableReason();
    }

    /**
     * Get a selectable reason object
     *
     * @param int 	$id
     * @param string 	$reason
     * @param bool 	$active
     *
     * @return SelectableReason
     */
    public function getSelectableReason($id, $reason, $active)
    {
        assert('is_int($id)');
        assert('is_string($reason)');
        assert('is_bool($active)');

        return $this->selectable_reasons_db->getSelectableReasonWith($id, $reason, $active);
    }

    /**
     * Create a new selectable reason
     *
     * @param string 	$reason
     * @param bool 	$active
     *
     * @return SelectableReason
     */
    public function createSelectableReason($reason, $active)
    {
        assert('is_string($reason)');
        assert('is_bool($active)');

        return $this->selectable_reasons_db->create($reason, $active);
    }

    /**
     * Delete a single selectable row by id
     *
     * @param int 	$id
     *
     * @return void
     */
    public function deleteSelectableReason($id)
    {
        assert('is_int($id)');
        $this->selectable_reasons_db->delete($id);
    }

    /**
     * Update a selectable reason
     *
     * @param SelectableReason 	$selectable_reason
     *
     * @return void
     */
    public function updateSelectableReason(SelectableReason $selectable_reason)
    {
        $this->selectable_reasons_db->update($selectable_reason);
    }

    /**
     * FilesStorage is used for the document on Modalities;
     *
     * @return Settings\DownloadableDocument\FilesStorage
     */
    public function getFileStorage()
    {
        return $this->document_file_storage;
    }

    /**
     * The db to map a role to the relevant doc.
     *
     * @return Settings\DownloadableDocument\DB
     */
    public function getDocumentRoleDB()
    {
        return $this->document_db;
    }

    /**
     * Get all roles and their respective doc.
     *
     * @return Settings\DownloadableDocument\Relevance[]
     */
    public function getDocumentRoleAssignments()
    {
        return $this->document_db->select();
    }

    /**
     * Update a doc-assignment
     *
     * @return void
     */
    public function updateDocumentRoleAssignment($relevance)
    {
        return $this->document_db->updateRoleSetting($relevance);
    }

    /**
     * Get the path to the downloadable document.
     * Return null, in none configured.
     *
     * @param int $usr_id
     * @return string | null
     */
    public function getModalitiesDocForUser($usr_id)
    {
        assert('is_int($usr_id)');
        $user_role_ids = $this->document_db->getGlobalRoleIdsForUser($usr_id);
        //take first role
        $role_id = array_shift($user_role_ids);
        $doc = $this->document_db->selectRoleSetting((int) $role_id);
        if (is_null($doc) || $doc->getFileName() === '') {
            $doc = $this->document_db->selectRoleSetting(0);
        }
        if (is_null($doc) || $doc->getFileName() === $this->getNoFileConst()) {
            return null;
        }
        return $this->getFileStorage()->getFilePath($doc->getFileName());
    }

    /**
     * @return string
     */
    public function getNoFileConst()
    {
        return self::MODALITIES_DOC_NO_FILE;
    }
}
