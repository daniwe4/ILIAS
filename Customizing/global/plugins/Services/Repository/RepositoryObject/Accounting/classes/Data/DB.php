<?php
namespace CaT\Plugins\Accounting\Data;

/**
 * Interface for DB handle of data values
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
     * Update data of an existing repo object.
     *
     * @param	Data		$data
     */
    public function update(Data $data);

    /**
     * return Data for $obj_id
     *
     * @param int $obj_id
     *
     * @return \CaT\Plugins\Accounting\Settings\Data
     */
    public function selectFor($obj_id);

    /**
     * Delete all information of the given obj id
     *
     * @param 	int 	$obj_id
     */
    public function deleteFor($obj_id);

    /**
     * Insert a tupel into the database
     *
     * @param Data 		$data 		single Data object
     */
    public function insert(Data $data);

    /**
     * Read all entries from database
     *
     * @return Data[]
     */
    public function read();
}
