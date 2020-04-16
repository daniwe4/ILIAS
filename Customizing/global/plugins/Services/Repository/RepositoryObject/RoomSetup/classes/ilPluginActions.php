<?php

declare(strict_types=1);

namespace CaT\Plugins\RoomSetup;

/**
 * Actions for communication to ilias only login is needing
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilPluginActions
{
    const F_SERVICE_OPTION_NAME = "service_option_name";
    const F_SERVICE_OPTION_ID = "service_option_id";
    const F_SERVICE_OPTION_ORG_NAME = "service_option_org_name";
    const F_DELETE_SERVICE_OPTION_IDS = "to_delete_ids";
    const F_SERVICE_OPTION_ACTIVE = "service_option_active";

    public function __construct(\ilRoomSetupPlugin $plugin, ServiceOptions\DB $service_option_db)
    {
        $this->plugin = $plugin;
        $this->service_option_db = $service_option_db;
    }

    /**
     * Get the plugin object
     * @return \ilRoomSetupPlugin
     */
    public function getPlugin()
    {
        return $this->plugin;
    }

    /**
     * Get all available service options
     * @return ServiceOption[]
     */
    public function getAllServiceOptions(int $offset, int $limit, string $order_field, string $order_direction) : array
    {
        return $this->service_option_db->selectAll($offset, $limit, $order_field, $order_direction);
    }

    /**
     * Get all available service options
     */
    public function getAllServiceOptionsCount() : int
    {
        return $this->service_option_db->selectAllCount();
    }

    /**
     * Get all active available service options
     * @return ServiceOption[]
     */
    public function getAllActiveServiceOptions() : array
    {
        return $this->service_option_db->selectAllActive();
    }

    /**
     * Get inactive assigned service options for GUI
     * @param int[] 	$missing
     * @return array<int, string>
     */
    public function getMissingAssignedInactiveOptions(array $missing) : array
    {
        $ret = array();
        foreach ($this->service_option_db->getMissingAssignedInactiveOptions($missing) as $service_option) {
            $ret[$service_option->getId()] = $service_option->getName();
        }

        return $ret;
    }

    /**
     * Create a service option entry
     * @return void
     */
    public function createServiceOption(string $service_option_name, bool $service_option_active)
    {
        assert('is_string($service_option_name)');
        assert('is_bool($service_option_active)');
        return $this->service_option_db->create($service_option_name, $service_option_active);
    }

    /**
     * Get form values for service option id
     * @return string[]
     */
    public function getServiceOptionsValues(int $id) : array
    {
        $service_option = $this->getServiceOptionById($id);

        $values = array();
        $values[self::F_SERVICE_OPTION_ID] = $service_option->getId();
        $values[self::F_SERVICE_OPTION_NAME] = $service_option->getName();
        $values[self::F_SERVICE_OPTION_ORG_NAME] = $service_option->getName();
        $values[self::F_SERVICE_OPTION_ACTIVE] = $service_option->getActive();

        return $values;
    }

    /**
     * Get a service option object
     */
    public function getServiceOptionById(int $id) : ServiceOptions\ServiceOption
    {
        return $this->service_option_db->select($id);
    }

    /**
     * Update an existing service option
     * @return void
     */
    public function updateServiceOption(ServiceOptions\ServiceOption $service_option)
    {
        $this->service_option_db->update($service_option);
    }

    /**
     * Delete a service option by id
     * @return void
     */
    public function deleteServiceOptionById(int $id)
    {
        $this->service_option_db->deleteById($id);
    }

    /**
     * Get available service option as array for form item
     * @return string[]
     */
    public function getServiceOptionsForFormItem() : array
    {
        $ret = array();
        foreach ($this->getAllActiveServiceOptions() as $service_option) {
            $ret[$service_option->getId()] = $service_option->getName();
        }

        return $ret;
    }

    /**
     * Create a new blanko service option with fixed id
     */
    public function getEmptyServiceOption() : ServiceOptions\ServiceOption
    {
        return new ServiceOptions\ServiceOption(-1, "", false);
    }

    /**
     * Create a service option for values
     * @return ServiceOptions\ServiceOption
     */
    public function getServiceOptionFor(int $id, string $name, bool $active) : ServiceOptions\ServiceOption
    {
        return new ServiceOptions\ServiceOption($id, $name, $active);
    }
}
