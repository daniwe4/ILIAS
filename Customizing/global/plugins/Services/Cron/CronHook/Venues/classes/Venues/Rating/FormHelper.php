<?php

declare(strict_types=1);

namespace CaT\Plugins\Venues\Venues\Rating;

use CaT\Plugins\Venues\Venues\ConfigFormHelper;
use CaT\Plugins\Venues\Venues\Venue;
use CaT\Plugins\Venues\ilActions;

require_once("Services/Form/classes/class.ilPropertyFormGUI.php");

/**
 * Assistant class for venu edit gui
 *
 * @author Stefan Hecken 	<stefan.heclen@concepts-and-training.de>
 */
class FormHelper implements ConfigFormHelper
{
    const F_RATING = "rating";
    const F_INFO = "info";

    /**
     * @var ilActions
     */
    protected $actions;

    /**
     * @var Closure
     */
    protected $txt;

    public function __construct(ilActions $actions, \Closure $txt)
    {
        $this->actions = $actions;
        $this->txt = $txt;
    }

    /**
     * @inheritdoc
     */
    public function addFormItems(\ilPropertyFormGUI $form)
    {
        $sh = new \ilFormSectionHeaderGUI();
        $sh->setTitle($this->txt("sh_rating"));
        $form->addItem($sh);

        $si = new \ilSelectInputGUI($this->txt("rating"), self::F_RATING);
        $options = array(null => "-") + $this->getRatingOptions();
        $si->setOptions($options);
        $form->addItem($si);

        $ta = new \ilTextareaInputGUI($this->txt("info"), self::F_INFO);
        $form->addItem($ta);
    }

    /**
     * @inheritdoc
     */
    public function createObject(int $venue_id, array $post)
    {
        $rating = 0.0;
        if (isset($post[self::F_RATING]) || trim($post[self::F_RATING]) != "") {
            $rating = floatval(trim($post[self::F_RATING]));
        }

        $this->actions->createRatingObject(
            $venue_id,
            $rating,
            trim($post[self::F_INFO])
        );
    }

    /**
     * @inheritdoc
     */
    public function getObject(int $venue_id, array $post)
    {
        $rating = 0.0;
        if (isset($post[self::F_RATING]) || trim($post[self::F_RATING]) != "") {
            $rating = floatval(trim($post[self::F_RATING]));
        }

        return $this->actions->getRatingObject(
            $venue_id,
            $rating,
            trim($post[self::F_INFO])
        );
    }

    /**
     * @inheritdoc
     */
    public function addValues(array &$values, Venue $venue)
    {
        $values[self::F_RATING] = $venue->getRating()->getRating();
        $values[self::F_INFO] = $venue->getRating()->getInfo();
    }

    /**
     * @return array<float, string>
     */
    protected function getRatingOptions() : array
    {
        return array("0.0" => $this->txt("no_stars")
                , "0.2" => $this->txt("one_star")
                , "0.4" => $this->txt("two_stars")
                , "0.6" => $this->txt("three_stars")
                , "0.8" => $this->txt("four_stars")
                , "1.0" => $this->txt("five_stars")
            );
    }

    /**
     * Translate lang var
     *
     * @param string 	$code
     *
     * @return string
     */
    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }
}
