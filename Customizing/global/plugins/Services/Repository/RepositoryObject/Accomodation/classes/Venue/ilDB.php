<?php
namespace CaT\Plugins\Accomodation\Venue;

use CaT\Plugins\Accomodation;

/**
 * DB handle of venues
 *
 * @author 	Nils Haagen 	<nils.haagen@concepts-and-training.de>
 */
class ilDB implements DB
{


    /**
    * @param bool 	$throw
    * @return plugin|false
    */
    private function getVenuePlug($throw = true)
    {
        if (\ilPluginAdmin::isPluginActive('venues') !== true) {
            if ($throw) {
                throw new \Exception('Venue plugin is not available');
            }
            return false;
        }
        return \ilPluginAdmin::getPluginObjectById('venues');
    }

    /**
     * Get available venues from the venue-plugin
     * with id=>title
     * @return  array<int,string>
     */
    public function getVenueListFromPlugin()
    {
        $plug = $this->getVenuePlug(false);
        if (!$plug) {
            return array();
        }
        $options = $plug->getActions()->getVenueOptions();
        uasort($options, function ($a, $b) {
            return strcmp($a, $b);
        });
        return $options;
    }

    /**
     * create and return a Venue from the values
     * of a venue described in the plugin
     *
     * @param int 	$venue_id
     * @return Venue | null
     */
    public function getVenueFromPlugin($venue_id)
    {
        $plug = $this->getVenuePlug();
        try {
            $venue = $plug->getActions()->getVenue($venue_id);
        } catch (\Exception $e) {
            return null;
        }

        return new Venue($venue, null);
    }

    /**
     * create and return a Venue as it is configured at the course
     *
     * @param int 	$course_id
     * @return Venue | null
     */
    public function getVenueFromCourse($course_id)
    {
        $plug = $this->getVenuePlug();
        $assignment = $plug->getActions()->getAssignment($course_id);

        if (!$assignment) {
            return null;
        }

        if ($assignment->isListAssignment()) {
            return $this->getVenueFromPlugin($assignment->getVenueId());
        }
        if ($assignment->isCustomAssignment()) {
            return new Venue(
                null,
                $assignment->getVenueText()
            );
        }
    }

    /**
     * Get a Venue-Object by course-id.
     * The "real" venue is the venue as issued by the Venues-Plugin.
     *
     * @param 	int 	$course_id
     * @return 	Venue | null
     */
    public function getRealVenueByCourseId($course_id)
    {
        assert('is_int($course_id)');
        $vplug = $this->getVenuePlug();
        $vactions = $vplug->getActions();
        $vassignment = $vactions->getAssignment($course_id);

        if (!$vassignment) {
            return null;
        }

        if ($vassignment->isListAssignment()) {
            $venue_id = $vassignment->getVenueId();
            if ((int) $venue_id == 0) {
                return null;
            }
            return $vactions->getVenue($venue_id);
        }
        return null;
    }

    /**
     * Get a Venue-Object by course-id.
     * The "real" venue is the venue as issued by the Venues-Plugin.
     *
     * @param 	int 	$venue_id
     * @return 	Venue
     */
    public function getRealVenueByVenueId($venue_id)
    {
        assert('is_int($venue_id)');
        $vplug = $this->getVenuePlug();
        $vactions = $vplug->getActions();
        return $vactions->getVenue($venue_id);
    }
}
