<?php declare(strict_types=1);

namespace CaT\Plugins\AgendaItemPool\AgendaItem;

trait Helper
{
    /**
     * Get agenda item with id from get.
     *
     * @return 	AgendaItem
     */
    public function getAgendaItem()
    {
        $id = $this->validateIdFromGet();
        return $this->object_actions->getAgendaItemById($id);
    }

    /**
     * Get id from $_GET and return the validated id.
     */
    public function validateIdFromGet() : int
    {
        if (isset($_GET['id'])) {
            return (int) $_GET['id'];
        }
        return -1;
    }

    /**
     * Get pool_id from $_GET and return the validated id.
     */
    public function validatePoolIdFromGet() : int
    {
        if (isset($_GET['pool_id'])) {
            return (int) $_GET['pool_id'];
        }
        return -1;
    }

    /**
     * Get all children by type recursive
     *
     * @return Object 	of search type
     */
    protected function getAllChildrenOfByType(int $ref_id, string $search_type)
    {
        $childs = $this->g_tree->getChilds($ref_id);
        $ret = array();

        foreach ($childs as $child) {
            $type = $child["type"];
            if ($type == $search_type) {
                $ret[] = \ilObjectFactory::getInstanceByRefId($child["child"]);
            }

            if ($this->g_objDefinition->isContainer($type)) {
                $rec_ret = $this->getAllChildrenOfByType((int) $child["child"], $search_type);
                if (!is_null($rec_ret)) {
                    $ret = array_merge($ret, $rec_ret);
                }
            }
        }

        return $ret;
    }

    /**
     * Compares ai by obj id
     */
    protected function compare_objects(AgendaItem $obj_a, AgendaItem $obj_b) : int
    {
        return $obj_a->getObjId() - $obj_b->getObjId();
    }

    /**
     * Sort an array of AgendaItem by column and direction.
     *
     * @param 	AgendaItem[] 	$ai
     * @param 	string 			$dir
     * @param 	string 			$column
     * @return 	AgendaItem[]
     */
    protected function sortAgendaItems(array $ai, string $dir, string $column) : array
    {
        switch ($column) {
            case self::S_TITLE:
                uasort($ai, function ($a, $b) use ($dir) {
                    if ($dir == "asc") {
                        return strcmp($a->getTitle(), $b->getTitle());
                    }
                    return strcmp($b->getTitle(), $a->getTitle());
                });
                break;
            case self::S_GOALS:
                uasort($ai, function ($a, $b) use ($dir) {
                    if ($dir == "asc") {
                        return strcmp($a->getGoals(), $b->getGoals());
                    }
                    return strcmp($b->getGoals(), $a->getGoals());
                });
                break;
            case self::S_GDV_CONTENT:
                uasort($ai, function ($a, $b) use ($dir) {
                    if ($dir == "asc") {
                        return strcmp(self::$gdv_content[$a->getGDVLearningContent()], self::$gdv_content[$b->getGDVLearningContent()]);
                    }
                    return strcmp(self::$gdv_content[$b->getGDVLearningContent()], self::$gdv_content[$a->getGDVLearningContent()]);
                });
                break;
            case self::S_IDD_CONTENT:
                uasort($ai, function ($a, $b) use ($dir) {
                    if ($dir == "asc") {
                        return strcmp($a->getIDDLearningContent(), $b->getIDDLearningContent());
                    }
                    return strcmp($b->getIDDLearningContent(), $a->getIDDLearningContent());
                });
                break;
            case self::S_IDD_RELEVANT:
                uasort($ai, function ($a, $b) use ($dir) {
                    if ($dir == "asc") {
                        return $this->sortBooleanAsc($a->getIddRelevant(), $b->getIddRelevant());
                    }
                    return $this->sortBooleanDesc($a->getIddRelevant(), $b->getIddRelevant());
                });
                break;
            case self::S_ACTIVE:
                uasort($ai, function ($a, $b) use ($dir) {
                    if ($dir == "asc") {
                        return $this->sortBooleanAsc($a->getIsActive(), $b->getIsActive());
                    }
                    return $this->sortBooleanDesc($a->getIsActive(), $b->getIsActive());
                });
                break;
            default:
                throw new \Exception("Unknown column: " . $column);
                break;
        }
        return $ai;
    }

    protected function sortBooleanAsc(bool $value1, bool $value2)
    {
        if ($value1 && !$value2) {
            return 1;
        }
        if (!$value1 && $value2) {
            return -1;
        }
        return 0;
    }

    protected function sortBooleanDesc(bool $value1, bool $value2)
    {
        if ($value1 && !$value2) {
            return -1;
        }
        if (!$value1 && $value2) {
            return 1;
        }
        return 0;
    }

    /**
     * Get ids of all used ai
     *
     * @return int[]
     */
    protected function getUsedAiIds() : array
    {
        $ret = array();
        if (!\ilPluginAdmin::isPluginActive("xage")) {
            return $ret;
        }

        $agendas = $this->getAllChildrenOfByType((int) ROOT_FOLDER_ID, "xage");
        foreach ($agendas as $agenda) {
            $entry_actions = $agenda->getAgendaEntryDB();
            foreach ($entry_actions->selectFor((int) $agenda->getId()) as $entry) {
                $ret[] = $entry->getPoolItemId();
            }
        }

        return array_unique($ret);
    }

    /**
     * Translate code to lang value
     */
    protected function txt(string $code) : string
    {
        $txt = $this->txt;
        return $txt($code);
    }
}
