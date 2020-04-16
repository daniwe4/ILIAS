<?php

namespace CaT\Plugins\EduBiography;

/**
 * Locates users and orgus visible to a viewer in the context of EduBio.
 */

class UserOrguLocator
{
    const VIEW_LP = "view_learning_progress";
    const VIEW_LP_RECURSIVE = "view_learning_progress_rec";

    protected $orgu_tree;

    /**
     * @var \ilAccess
     */
    protected $access;

    public function __construct(
        \ilObjOrgUnitTree $orgu_tree,
        \ilAccess $access,
        \TMSPositionHelper $pos_helper
    ) {
        $this->orgu_tree = $orgu_tree;
        $this->access = $access;
        $this->pos_helper = $pos_helper;
    }

    /**
     * Get all user ids, that are visible to $usr in the edu-biography.
     *
     * @param	\ilObjUser	$usr
     * @return	int[]
     */
    public function getVisibleUserIds(\ilObjUser $usr)
    {
        $usr_id = $usr->getId();
        $positions = $this->pos_helper->getPositionsOfUserWithAuthority($usr_id);
        $orgus = $this->pos_helper->getOrgUnitByPositions($positions, $usr_id);

        $visible_user_ids = $this->pos_helper->getAllVisibleUserIdsForUser($usr_id, $this->orgu_tree);
        return($visible_user_ids);
    }

    /**
     * Is user having $usr_idvisible to $usr?
     *
     * @param	int	$usr_id
     * @param	\ilObjUser	$usr
     * @return	bool
     */
    public function isUserIdVisibleToUser($usr_id, \ilObjUser $usr)
    {
        assert('is_int($usr_id)');
        if ($usr_id === (int) $usr->getId()) {
            return true;
        }
        return in_array($usr_id, $this->getVisibleUserIds($usr));
    }

    /**
     * Get all orgus visible to user, i.e. in which the user is superior or
     * respective child orgus.
     *
     * @return	string[int]		ref_id => title
     */
    public function orgusVisibleToUser(\ilObjUser $usr)
    {
        $usr_id = $usr->getId();
        $positions = $this->pos_helper->getPositionsOfUserWithAuthority($usr_id);
        $orgu_refs = $this->pos_helper->getOrgUnitByPositions($positions, $usr_id);
        $children = [];
        foreach ($orgu_refs as $orgu_ref) {
            $children = array_merge($children, $this->orgu_tree->getAllChildren($orgu_ref));
        }
        return $this->orgu_tree->getTitles(array_map(
            function ($intlike_val) {
                return (int) $intlike_val;
            },
            array_unique(array_merge($orgu_refs, $children))
        ));
    }

    /**
     * Get all orgu children of
     *
     * @param int 	$orgu_ref_id
     *
     * @return int[]
     */
    public function getChildrenOf($orgu_ref_id)
    {
        return $this->orgu_tree->getAllChildren($orgu_ref_id);
    }
}
