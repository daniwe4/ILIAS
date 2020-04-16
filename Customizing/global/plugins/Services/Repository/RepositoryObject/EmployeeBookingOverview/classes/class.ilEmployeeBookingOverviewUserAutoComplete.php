<?php

class ilEmployeeBookingOverviewUserAutoComplete extends ilUserAutoComplete
{
    protected $visible_users = [];

    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        parent::__construct();
        $this->db = $db;
    }


    public function withVisibleUsers(array $visible_users)
    {
        $other = clone $this;
        $other->visible_users = $visible_users;
        return $other;
    }

    protected function getWherePart(array $search_query)
    {
        $where = parent::getWherePart($search_query);
        if (trim($where) !== '') {
            $where .= ' AND ';
        }
        $where .= count($this->visible_users) > 0 ?
            $this->db->in('ud.usr_id', $this->visible_users, false, 'integer') :
            'FALSE';
        $GLOBALS['DIC']['log']->dump($where);
        return $where;
    }
}
