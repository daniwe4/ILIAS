<?php

declare(strict_types=1);

namespace CaT\Plugins\Venues\Venues\Costs;

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
    const F_FIXED_RATE_DAY = "f_fixed_rate_day";
    const F_FIXED_RATE_ALL_INCLUSIVE = "f_fixed_rate_all_inclusive";
    const F_BED_AND_BREAKFAST = "f_bed_and_breakfast";
    const F_BED = "f_bed";
    const F_FIXED_RATE_CONFERENCE = "f_fixed_rate_conference";
    const F_ROOM_USAGE = "f_room_usage";
    const F_OTHER = "f_other";
    const F_TERMS = "f_costs_terms";

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
        $sh->setTitle($this->txt("sh_costs"));
        $form->addItem($sh);

        $ni = new \ilNumberInputGUI($this->txt("fixed_rate_day"), self::F_FIXED_RATE_DAY);
        $ni->allowDecimals(true);
        $ni->setMinValue(0, true);
        $form->addItem($ni);

        $ni = new \ilNumberInputGUI($this->txt("fixed_rate_all_inclusive"), self::F_FIXED_RATE_ALL_INCLUSIVE);
        $ni->allowDecimals(true);
        $ni->setMinValue(0, true);
        $form->addItem($ni);

        $ni = new \ilNumberInputGUI($this->txt("bed_and_breakfast"), self::F_BED_AND_BREAKFAST);
        $ni->allowDecimals(true);
        $ni->setMinValue(0, true);
        $form->addItem($ni);

        $ni = new \ilNumberInputGUI($this->txt("bed"), self::F_BED);
        $ni->allowDecimals(true);
        $ni->setMinValue(0, true);
        $form->addItem($ni);

        $ni = new \ilNumberInputGUI($this->txt("fixed_rate_conference"), self::F_FIXED_RATE_CONFERENCE);
        $ni->allowDecimals(true);
        $ni->setMinValue(0, true);
        $form->addItem($ni);

        $ni = new \ilNumberInputGUI($this->txt("room_usage"), self::F_ROOM_USAGE);
        $ni->allowDecimals(true);
        $ni->setMinValue(0, true);
        $form->addItem($ni);

        $ni = new \ilNumberInputGUI($this->txt("other"), self::F_OTHER);
        $ni->allowDecimals(true);
        $ni->setMinValue(0, true);
        $form->addItem($ni);

        $ta = new \ilTextareaInputGUI($this->txt("costs_terms"), self::F_TERMS);
        $form->addItem($ta);
    }

    /**
     * @inheritdoc
     */
    public function createObject(int $venue_id, array $post)
    {
        $fixed_rate_day = $this->emptyToFloat($post[self::F_FIXED_RATE_DAY]);
        $fixed_rate_all_inclusive = $this->emptyToFloat($post[self::F_FIXED_RATE_ALL_INCLUSIVE]);
        $bed_and_breakfast = $this->emptyToFloat($post[self::F_BED_AND_BREAKFAST]);
        $bed = $this->emptyToFloat($post[self::F_BED]);
        $fixed_rate_conference = $this->emptyToFloat($post[self::F_FIXED_RATE_CONFERENCE]);
        $room_usage = $this->emptyToFloat($post[self::F_ROOM_USAGE]);
        $other = $this->emptyToFloat($post[self::F_OTHER]);

        $this->actions->createCostsObject(
            $venue_id,
            $fixed_rate_day,
            $fixed_rate_all_inclusive,
            $bed_and_breakfast,
            $bed,
            $fixed_rate_conference,
            $room_usage,
            $other,
            trim($post[self::F_TERMS])
        );
    }

    /**
     * @inheritdoc
     */
    public function getObject(int $venue_id, array $post)
    {
        $fixed_rate_day = $this->emptyToFloat($post[self::F_FIXED_RATE_DAY]);
        $fixed_rate_all_inclusive = $this->emptyToFloat($post[self::F_FIXED_RATE_ALL_INCLUSIVE]);
        $bed_and_breakfast = $this->emptyToFloat($post[self::F_BED_AND_BREAKFAST]);
        $bed = $this->emptyToFloat($post[self::F_BED]);
        $fixed_rate_conference = $this->emptyToFloat($post[self::F_FIXED_RATE_CONFERENCE]);
        $room_usage = $this->emptyToFloat($post[self::F_ROOM_USAGE]);
        $other = $this->emptyToFloat($post[self::F_OTHER]);

        return $this->actions->getCostsObject(
            $venue_id,
            $fixed_rate_day,
            $fixed_rate_all_inclusive,
            $bed_and_breakfast,
            $bed,
            $fixed_rate_conference,
            $room_usage,
            $other,
            trim($post[self::F_TERMS])
        );
    }

    /**
     * @inheritdoc
     */
    public function addValues(array &$values, Venue $venue)
    {
        $values[self::F_FIXED_RATE_DAY] = $venue->getCosts()->getFixedRateDay();
        $values[self::F_FIXED_RATE_ALL_INCLUSIVE] = $venue->getCosts()->getFixedRateAllInclusiv();
        $values[self::F_BED_AND_BREAKFAST] = $venue->getCosts()->getBedAndBreakfast();
        $values[self::F_BED] = $venue->getCosts()->getBed();
        $values[self::F_FIXED_RATE_CONFERENCE] = $venue->getCosts()->getFixedRateConference();
        $values[self::F_ROOM_USAGE] = $venue->getCosts()->getRoomUsage();
        $values[self::F_OTHER] = $venue->getCosts()->getOther();
        $values[self::F_TERMS] = $venue->getCosts()->getTerms();
    }

    /**
     * Change empty string to null or float
     *
     * @return float | null
     */
    protected function emptyToFloat(string $value)
    {
        if ($value == "") {
            return null;
        }

        return floatval(str_replace(",", ".", $value));
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
