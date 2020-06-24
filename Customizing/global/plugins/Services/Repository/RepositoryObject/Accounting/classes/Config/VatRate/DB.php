<?php
namespace CaT\Plugins\Accounting\Config\VatRate;

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
     * @param	VatRate		$vat_rate
     */
    public function update(VatRate $vat_rate);

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
     * @param float 	$value
     * @param string 	$label
     * @param bool 		$active
     *
     * @return VatRate
     */
    public function insert($value, $label, $active);

    /**
     * Read all entries from database
     *
     * @return VatRate[]
     */
    public function read();

    /**
     * Get the value of selected vatrate
     *
     * @param int 	$id
     *
     * @return float
     */
    public function getVatRateValueById(int $id) : float;
}
