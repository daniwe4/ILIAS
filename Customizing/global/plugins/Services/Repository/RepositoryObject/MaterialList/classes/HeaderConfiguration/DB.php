<?php

namespace CaT\Plugins\MaterialList\HeaderConfiguration;

/**
 * Execute all option for a configuration entry in database
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
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
     * @param ConfigurationEntry 	$configuration_entry
     */
    public function update(ConfigurationEntry $configuration_entry);

    /**
     * Create a new settings object for ConfigurationEntry object.
     *
     * @param	string		$type
     * @param	string|int		$source_for_value
     *
     * @return \CaT\Plugins\MaterialList\HeaderConfiguration\ConfigurationEntry
     */
    public function create(string $type, $source_for_value);

    /**
     * Return all defined configuration entries
     *
     * @return \CaT\Plugins\MaterialList\HeaderConfiguration\ConfigurationEntry[]
     */
    public function selectAll();

    /**
     * Delete all information of the given id
     *
     * @param 	int 	$id
     */
    public function delete(int $id);
}
