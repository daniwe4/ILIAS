<?php

namespace CaT\Plugins\Accomodation\Venue;

/**
 * A venue as it is used by this plugin.
 *
 * @author 	Nils Haagen 	<nils.haagen@concepts-and-training.de>
 */
class Venue
{

    /**
     * @var Venue|null
     */
    protected $venue;

    /**
     * @var string|null
     */
    protected $venue_text;

    /**
     * Construct a venue
     *
     * @param Venue|null 	$venue
     * @param string|null 	$venue_text
     */
    public function __construct($venue, ?string $venue_text)
    {
        //2do: make sure either one is set.
        $this->venue = $venue;
        $this->venue_text = $venue_text;
    }

    /**
     * get the id of this venue
     * @return int|null
     */
    public function getObjId()
    {
        if (is_null($this->venue)) {
            return null;
        }
        return $this->venue->getObjId();
    }

    /**
     * get the name of this venue
     * @return string
     */
    public function getName()
    {
        if (is_null($this->venue)) {
            return trim(explode("\n", trim($this->venue_text))[0]);
        }
        return $this->venue->getGeneral()->getName();
    }

    /**
     * get the (HTML) presentation of the venue
     * @return string
     */
    public function getHTML(string $separator = '<br />')
    {
        if (is_null($this->venue)) {
            return $this->venue_text;
        }

        $gen = $this->venue->getGeneral();
        $add = $this->venue->getAddress();
        $con = $this->venue->getContact();

        $venue_text = array(
            $gen->getName(),
            $add->getAddress1(),
            $add->getAddress2(),
            $add->getPostcode() . ' ' . $add->getCity(),
            $con->getPhone(),
            $con->getEmail(),
            $gen->getHomepage()
        );
        $venue_text = array_filter($venue_text, function ($val) {
            return trim($val) !== '';
        });
        $venue_text = implode($separator, $venue_text);
        return $venue_text;
    }
}
