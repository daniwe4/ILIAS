<?php
namespace CaT\Plugins\Accounting\Config\CostType;

/**
 * Interface for DB handle of additional config values
 *
 * @author Daniel Weise 	<daniel.weise@concepts-and-training.de>
 */
interface DB
{
    /**
     * Install tables and standard contents and types.
     */
    public function install();

    /**
     * Update settings of an existing repo object.
     *
     * @param	CostType		$settings
     */
    public function update(CostType $costtype);

    /**
     * Delete all information of the given obj id
     *
     * @param 	int 	$obj_id
     */
    public function deleteFor($obj_id);

    /**
     * Get an array for the selection box with (value => name) pairs
     *
     * @return array<string, string>
     */
    public function getSelectionArray();

    /**
     * Insert a tupel into the database
     *
     * @param string 	$label
     * @param string 	$value
     * @param bool 		$active
     *
     * @return CostType
     */
    public function insert($label, $value, $active);

    /**
     * Read all entries from database
     *
     * @return CostType[]
     */
    public function read();
}
