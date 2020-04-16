<?php

namespace CaT\Plugins\RoomSetup;

/**
 * Inteface for the pluginobject to make it more testable
 */
interface ObjRoomSetup
{
    /**
     * Get the title of object
     *
     * @return string
     */
    public function getTitle();

    /**
     * Get the description of object
     *
     * @return string
     */
    public function getDescription();

    /**
     * Set the title of object
     *
     * @param string 	$a_title
     *
     * @return null
     */
    public function setTitle($a_title);

    /**
     * Set the description of object
     *
     * @param string 	$a_desc
     *
     * @return null
     */
    public function setDescription($a_desc);

    /**
     * Update the object
     *
     * @return null
     */
    public function update();

    /**
     * @param	\Closure	$update		function from Settings/MaterialList to Settings/MaterialList
     * @return	null
     */
    public function updateSettings(\Closure $update);

    /**
     * Get the settings of the object
     *
     * @throws \LogicException if object is not properly initialized.
     * @return Settings\MaterialList
     */
    public function getSettings();
}
