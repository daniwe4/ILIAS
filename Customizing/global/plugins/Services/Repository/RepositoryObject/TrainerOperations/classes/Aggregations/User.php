<?php

declare(strict_types=1);

namespace CaT\Plugins\TrainerOperations\Aggregations;

/**
 * Get information about a user
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class User
{
    /**
     * @var \ilObjUser
     */
    protected $current_user;

    public function __construct(\ilObjUser $current_user)
    {
        $this->current_user = $current_user;
    }

    public function getName(int $usr_id) : string
    {
        return \ilObjUser::_lookupFullname($usr_id);
    }

    public function getLogin(int $usr_id) : string
    {
        return \ilObjUser::_lookupLogin($usr_id);
    }

    public function getSkin(int $usr_id) : string
    {
        $usr = new \ilObjUser($usr_id);
        return $usr->skin;
    }

    public function getCurrentUserId() : int
    {
        return (int) $this->current_user->getId();
    }

    public function sortUsrIdsByUserLastname(array $usr_ids) : array
    {
        usort($usr_ids, function ($a, $b) {
            $a_usr = new \ilObjUser($a);
            $b_usr = new \ilObjUser($b);
            return strcasecmp($a_usr->getLastname(), $b_usr->getLastname());
        });
        return $usr_ids;
    }

    public function getDisplayName(int $usr_id, bool $include_login = false) : string
    {
        $usr = new \ilObjUser($usr_id);
        $ret = $usr->getLastname() . ', ' . $usr->getFirstname();
        if ($include_login) {
            $ret .= ' (' . $usr->getLogin() . ')';
        }
        return $ret;
    }

    /**
     * store user-prefs like interval and selected columns
     */
    public function setCurrentUserPrefsForTEP(int $tep_id, array $params)
    {
        $pref = serialize($params);
        $this->current_user->setPref($this->getPrefKey($tep_id), $pref);
        $this->current_user->writePrefs();
    }

    public function getCurrentUserPrefsForTEP(int $tep_id) : array
    {
        $pref = $this->current_user->getPref($this->getPrefKey($tep_id));
        if (!$pref) {
            return [];
        }
        return unserialize($pref);
    }

    protected function getPrefKey(int $tep_id)
    {
        return 'tep_' . (string) $tep_id;
    }
}
