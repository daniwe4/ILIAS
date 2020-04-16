<?php

declare(strict_types=1);

namespace CaT\Plugins\TrainerOperations\Aggregations;

use CaT\Plugins\TrainerOperations\Settings;

/**
 * Access OrgUnits and roles to check for authority over users
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class UserAuthority
{
    /**
     * @var \TMSPositionHelper
     */
    protected $pos_helper;

    /**
     * @var \ilRbacReview
     */
    protected $rbac_review;

    /**
     * @var Settings\DB
     */
    protected $tep_settings;

    /**
     * @var int
     */
    protected $tep_obj_id;
    /**
     * @var int
     */
    protected $current_user_id;

    public function __construct(
        \TMSPositionHelper $pos_helper,
        \ilRbacReview $rbacreview,
        Settings\DB $tep_settings_db,
        int $tep_obj_id,
        int $current_user_id
    ) {
        $this->pos_helper = $pos_helper;
        $this->rbac_review = $rbacreview;
        $this->tep_settings_db = $tep_settings_db;
        $this->tep_obj_id = $tep_obj_id;
        $this->current_user_id = $current_user_id;
    }

    /**
     * @return int[]
     */
    public function getTrainers() : array
    {
        $settings = $this->getSettingsForTep($this->tep_obj_id);
        $configured_roles = $settings->getGlobalRoles();
        $possible_users = $this->getAllUsersUnderAuthority($this->getCurrentUserId());

        $ret = [];
        foreach ($possible_users as $usr_id) {
            $usr_roles = $this->getGlobalRolesForUser($usr_id);
            $match = array_intersect($configured_roles, $usr_roles);
            if (count($match) > 0) {
                $ret[] = $usr_id;
            }
        }
        if (!in_array($this->current_user_id, $ret)) {
            $ret[] = $this->current_user_id;
        }

        return $ret;
    }

    protected function getSettingsForTep(int $tep_obj_id) : Settings\Settings
    {
        return $this->tep_settings_db->selectFor($tep_obj_id);
    }

    protected function getCurrentUserId() : int
    {
        return $this->current_user_id;
    }

    /**
     * @return int[]
     */
    protected function getAllUsersUnderAuthority(int $usr_id) : array
    {
        return $this->pos_helper->getUserIdWhereUserHasAuhtority($usr_id);
    }

    /**
     * @return int[]
     */
    protected function getGlobalRolesForUser(int $usr_id) : array
    {
        return $this->rbac_review->assignedRoles($usr_id);
    }
}
