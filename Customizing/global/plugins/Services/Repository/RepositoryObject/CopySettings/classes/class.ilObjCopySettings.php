<?php

declare(strict_types=1);

include_once("Services/Repository/classes/class.ilObjectPlugin.php");

use CaT\Plugins\CopySettings;

use CaT\Ente\ILIAS\ilProviderObjectHelper;

/**
 * Object of the plugin
 */
class ilObjCopySettings extends ilObjectPlugin
{
    use ilProviderObjectHelper;

    /**
     * @inheritdoc
     */
    protected function getDIC()
    {
        return $GLOBALS["DIC"];
    }

    protected static $parent_types = array("crs", "cat");

    /**
     * Init the type of the plugin. Same value as choosen in plugin.php
     */
    public function initType()
    {
        $this->setType("xcps");
    }

    /**
     * @inheritdoc
     */
    public function doCreate()
    {
        $this->getSettingsActions()->create();
        $this->createUnboundProvider("crs", CaT\Plugins\CopySettings\UnboundProvider::class, ILIAS_ABSOLUTE_PATH . "/Customizing/global/plugins/Services/Repository/RepositoryObject/CopySettings/classes/UnboundProvider.php");
    }

    /**
     * Get called if the object get be updated
     * Update additoinal setting values
     */
    public function doUpdate()
    {
        $this->getSettingsActions()->update($this->extended_settings);
        $this->raiseEvent('updateCopySettings');
    }

    /**
     * Get called after object creation to read further information
     */
    public function doRead()
    {
        $this->settings = $this->getActions()->select();
        $this->extended_settings = $this->getSettingsActions()->select();
    }

    /**
     * @inheritdoc
     */
    protected function beforeDelete()
    {
        $parent = $this->getParentContainer();
        if ($parent !== null) {
            $title = $parent->getTitle();
            $title = str_replace($this->getPlugin()->txt("template_prefix") . ": ", "", $title);
            $parent->setTitle($title);
            $parent->update();
        }

        return true;
    }

    /**
     * Get called if the object should be deleted.
     * Delete additional settings
     */
    public function doDelete()
    {
        $this->deleteUnboundProviders();
        $this->getActions()->clearCopySettings();
        $this->getSettingsActions()->delete();
        $this->getTemplateCoursesDB()->deleteFor((int) $this->getId());
        $this->raiseEvent('deleteCopySettings');
    }

    /**
     * Get called if the object get be coppied.
     * Copy additional settings to new object
     */
    public function doCloneObject($new_obj, $a_target_id, $a_copy_id = null)
    {
        $new_obj->doRead();
        $parent = $new_obj->getParentContainer();
        $title = $parent->getTitle();
        $parent->setTitle($this->txt("template_prefix") . ": " . $title);
        $parent->update();

        $new_obj->getTemplateCoursesDB()->create((int) $new_obj->getId(), (int) $parent->getId(), (int) $parent->getRefId());

        $ext_settings = $this->getExtendedSettings();
        $fnc = function (CopySettings\Settings\Settings $es) use ($ext_settings) {
            return $es->withEditTitle($ext_settings->getEditTitle())
                ->withEditTargetGroups($ext_settings->getEditTargetGroups())
                ->withEditTargetGroupDescription($ext_settings->getEditTargetGroupDescription())
                ->withEditContent($ext_settings->getEditContent())
                ->withEditBenefits($ext_settings->getEditBenefits())
                ->withEditIDDLearningTime($ext_settings->getEditIDDLearningTime())
                ->withRoleIds($ext_settings->getRoleIds())
                ->withTimeMode($ext_settings->getTimeMode())
                ->withMinDaysInFuture($ext_settings->getMinDaysInFuture())
                ->withAdditionalInfos($ext_settings->getAdditionalInfos())
                ->withNoMail($ext_settings->getNoMail())
                ->withSuppressMailDelivery($ext_settings->getSuppressMailDelivery())
                ->withEditGti($ext_settings->isEditGti())
                ->withEditVenue($ext_settings->getEditVenue())
                ->withEditProvider($ext_settings->getEditProvider())
                ->withEditMemberlimits($ext_settings->getEditMemberlimits())
            ;
        };
        $new_obj->updateExtendedSettings($fnc);
        $new_obj->doUpdate();
    }

    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Get the extended settings like edit_title or edit_agenda
     *
     * @return CopySettings\Settings\Settings
     */
    public function getExtendedSettings()
    {
        return $this->extended_settings;
    }

    /**
     * Assign creator to defined role at target course
     *
     * @param int 	$user_id
     * @param int 	$target_crs_ref_id
     *
     * @return void
     */
    public function workLocalRolesForUsers(int $user_id, int $target_crs_ref_id)
    {
        $target_crs = \ilObjectFactory::getInstanceByRefId($target_crs_ref_id);
        $defined_roles = $this->getExtendedSettings()->getRoleIds();
        $members_object = $target_crs->getMembersObject();
        $members_object->delete($user_id);

        require_once($this->getPluginDirectory() . "/classes/Settings/class.ilCopySettingsGUI.php");

        if (count($defined_roles) == 0) {
            $this->copyCourseAdminToTarget($members_object);
        } else {
            foreach ($defined_roles as $defined_role) {
                $role_id = null;
                switch ($defined_role) {
                    case \ilCopySettingsGUI::ROLE_ADMIN:
                        $role_id = IL_CRS_ADMIN;
                        break;
                    case \ilCopySettingsGUI::ROLE_TUTOR:
                        $role_id = IL_CRS_TUTOR;
                        if (!in_array(\ilCopySettingsGUI::ROLE_ADMIN, $defined_roles)) {
                            $this->copyCourseAdminToTarget($members_object);
                        }
                        break;
                    case \ilCopySettingsGUI::ROLE_MEMBER:
                        if (!in_array(\ilCopySettingsGUI::ROLE_ADMIN, $defined_roles)) {
                            $this->copyCourseAdminToTarget($members_object);
                        }
                        global $DIC;
                        $rbacadmin = $DIC["rbacadmin"];
                        $member_role = $target_crs->getDefaultMemberRole();
                        $rbacadmin->assignUser($member_role, $user_id);
                        break;
                }

                if (!is_null($role_id)) {
                    $members_object->add($user_id, $role_id, true);
                }
            }
        }
    }

    protected function copyCourseAdminToTarget(ilCourseParticipants $members_object)
    {
        $crs = $this->getParentCourse();
        foreach ($crs->getMembersObject()->getAdmins() as $admin) {
            if (!$members_object->isAdmin($admin)) {
                $members_object->add($admin, IL_CRS_ADMIN);
            }
        }
    }

    /**
     * Get the parent container
     *
     * @return ilObjCourse | null
     */
    public function getParentContainer()
    {
        global $DIC;
        $tree = $DIC->repositoryTree();
        $parents = $tree->getPathFull($this->getRefId());
        $parents = array_filter($parents, function ($p) {
            if (in_array($p["type"], self::$parent_types)) {
                return $p;
            }
        });

        if (count($parents) > 0) {
            $parent_crs = array_pop($parents);
            require_once("Services/Object/classes/class.ilObjectFactory.php");
            return ilObjectFactory::getInstanceByRefId($parent_crs["ref_id"]);
        }
        return null;
    }

    public function getParentCourse()
    {
        global $DIC;
        $tree = $DIC->repositoryTree();
        $parents = $tree->getPathFull($this->getRefId());
        $parents = array_filter($parents, function ($p) {
            if (in_array($p["type"], array("crs"))) {
                return $p;
            }
        });

        if (count($parents) > 0) {
            $parent_crs = array_pop($parents);
            require_once("Services/Object/classes/class.ilObjectFactory.php");
            return ilObjectFactory::getInstanceByRefId($parent_crs["ref_id"]);
        }
        return null;
    }

    /**
     * Get an instance of object actions
     *
     * @return ilObjectActions
     */
    public function getActions()
    {
        if ($this->actions === null) {
            global $DIC;
            $db = $DIC->database();

            $this->actions = new CopySettings\ilObjectActions($this, $this->getChildrenSettingsDB($db));
        }

        return $this->actions;
    }

    /**
     * Get an instance of settings actions
     *
     * @return CopySettings\Settings\ilActions
     */
    public function getSettingsActions()
    {
        if ($this->settings_actions === null) {
            global $DIC;
            $db = $DIC->database();

            $this->settings_actions = new CopySettings\Settings\ilActions($this, $this->getSettingsDB($db));
        }

        return $this->settings_actions;
    }

    /**
     * Get instance of child db
     *
     * @return CopySettings\Children\DB
     */
    protected function getChildrenSettingsDB($db)
    {
        if ($this->children_db === null) {
            $this->children_db = new CopySettings\Children\ilDB($db);
        }

        return $this->children_db;
    }

    /**
     * Get instance of settings db
     *
     * @return CopySettings\Settings\DB
     */
    protected function getSettingsDB($db)
    {
        if ($this->settings_db === null) {
            $this->settings_db = new CopySettings\Settings\ilDB($db);
        }

        return $this->settings_db;
    }

    /**
     * Get instance of template course db
     *
     * @return CopySettings\TemplateCourses\DB
     */
    public function getTemplateCoursesDB()
    {
        if ($this->template_courses_db === null) {
            global $DIC;
            $db = $DIC->database();
            $this->template_courses_db = new CopySettings\TemplateCourses\ilDB($db);
        }

        return $this->template_courses_db;
    }

    /**
     * Update exisiting settings
     *
     * @param \Closure 	$fnc
     *
     * @return void
     */
    public function updateExtendedSettings(\Closure $fnc)
    {
        $this->extended_settings = $fnc($this->extended_settings);
    }

    /**
     * Get closure for plugin lang
     *
     * @return Closure
     */
    public function txtClosure()
    {
        return function ($code) {
            return $this->getPlugin()->txt($code);
        };
    }

    public function getPluginDirectory()
    {
        return $this->getPlugin()->getDirectory();
    }


    /**
     * Raise an event of type $type. Attach type of event and parent
     * object.
     *
     * @param	string	type
     */
    public function raiseEvent(string $type)
    {
        $payload =
            [
                'parent' => $this->getParentContainer(),
                'type' => $type,
                'ref_id' => $this->getRefId()
            ];
        $this->getDIC()["ilAppEventHandler"]->raise(
            'Plugin/CopySettings',
            $type,
            $payload
        );
    }
}
