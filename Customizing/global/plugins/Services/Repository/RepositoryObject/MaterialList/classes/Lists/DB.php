<?php

namespace CaT\Plugins\MaterialList\Lists;

/**
 * Interface for repo object material list entries
 */
interface DB
{
    /**
     * Install tables etc.
     *
     * @return null
     */
    public function install();

    /**
     * Create a new list entry
     *
     * @param int 		$obj_id
     * @param int 		$number_per_participant
     * @param int 		$number_per_course
     * @param string 	$article_number
     * @param string 	$title
     *
     * @return ListEntry
     */
    public function create($obj_id, $number_per_participant, $number_per_course, $article_number, $title);

    /**
     * Update an existing list entry
     *
     * @param ListEntry
     *
     * @return null
     */
    public function update(ListEntry $list_entry);

    /**
     * Delete a single list entry by id
     *
     * @param int 		$id
     *
     * @return null
     */
    public function deleteId($id);

    /**
     * Delete list entries for obj
     *
     * @param int 		$obj_id
     *
     * @return 	null
     */
    public function deleteForObjId($obj_id);

    /**
     * Get list entries for obj
     *
     * @param int 		$obj_id
     *
     * @return ListEntry[] | []
     */
    public function selectForObjId($obj_id);
}
