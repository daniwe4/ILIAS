<?php
namespace CaT\Plugins\ScaledFeedback;

/**
 * Interface for the pluginobject to make it more testable
 */
interface ObjScaledFeedback
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
}
