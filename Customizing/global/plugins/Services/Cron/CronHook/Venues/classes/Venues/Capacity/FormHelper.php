<?php

declare(strict_types=1);

namespace CaT\Plugins\Venues\Venues\Capacity;

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
    const F_NUMBER_ROOMS = "number_rooms";
    const F_MIN_PERSON_ANY_ROOM = "min_number_any_room";
    const F_MAX_PERSON_ANY_ROOM = "max_number_any_room";
    const F_MIN_ROOM_SIZE = "min_room_size";
    const F_MAX_ROOM_SIZE = "max_room_size";
    const F_ROOM_COUNT = "room_count";

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
        $sh->setTitle($this->txt("sh_capacity"));
        $form->addItem($sh);

        $ni = new \ilNumberInputGUI($this->txt("number_rooms"), self::F_NUMBER_ROOMS);
        $ni->setMinValue(0, true);
        $form->addItem($ni);

        $ni = new \ilNumberInputGUI($this->txt("min_person_any_room"), self::F_MIN_PERSON_ANY_ROOM);
        $ni->setMinValue(0, true);
        $form->addItem($ni);

        $ni = new \ilNumberInputGUI($this->txt("max_person_any_room"), self::F_MAX_PERSON_ANY_ROOM);
        $ni->setMinValue(0, true);
        $form->addItem($ni);

        $ni = new \ilNumberInputGUI($this->txt("room_count"), self::F_ROOM_COUNT);
        $ni->setMinValue(0, true);
        $form->addItem($ni);

        $ni = new \ilNumberInputGUI($this->txt("min_room_size"), self::F_MIN_ROOM_SIZE);
        $ni->setMinValue(0, true);
        $form->addItem($ni);

        $ni = new \ilNumberInputGUI($this->txt("max_room_size"), self::F_MAX_ROOM_SIZE);
        $ni->setMinValue(0, true);
        $form->addItem($ni);
    }

    /**
     * @inheritdoc
     */
    public function createObject(int $venue_id, array $post)
    {
        $number_rooms = $this->emptyToInt($post[self::F_NUMBER_ROOMS]);
        $min_person_any_room = $this->emptyToInt($post[self::F_MIN_PERSON_ANY_ROOM]);
        $max_person_any_room = $this->emptyToInt($post[self::F_MAX_PERSON_ANY_ROOM]);
        $min_room_size = $this->emptyToInt($post[self::F_MIN_ROOM_SIZE]);
        $max_room_size = $this->emptyToInt($post[self::F_MAX_ROOM_SIZE]);
        $room_count = $this->emptyToInt($post[self::F_ROOM_COUNT]);

        $this->actions->createCapacityObject(
            $venue_id,
            $number_rooms,
            $min_person_any_room,
            $max_person_any_room,
            $min_room_size,
            $max_room_size,
            $room_count
        );
    }

    /**
     * @inheritdoc
     */
    public function getObject(int $venue_id, array $post)
    {
        $number_rooms = $this->emptyToInt($post[self::F_NUMBER_ROOMS]);
        $min_person_any_room = $this->emptyToInt($post[self::F_MIN_PERSON_ANY_ROOM]);
        $max_person_any_room = $this->emptyToInt($post[self::F_MAX_PERSON_ANY_ROOM]);
        $min_room_size = $this->emptyToInt($post[self::F_MIN_ROOM_SIZE]);
        $max_room_size = $this->emptyToInt($post[self::F_MAX_ROOM_SIZE]);
        $room_count = $this->emptyToInt($post[self::F_ROOM_COUNT]);

        return $this->actions->getCapacityObject(
            $venue_id,
            $number_rooms,
            $min_person_any_room,
            $max_person_any_room,
            $min_room_size,
            $max_room_size,
            $room_count
        );
    }

    /**
     * @inheritdoc
     */
    public function addValues(array &$values, Venue $venue)
    {
        $values[self::F_NUMBER_ROOMS] = $venue->getCapacity()->getNumberRoomsOvernights();
        $values[self::F_MIN_PERSON_ANY_ROOM] = $venue->getCapacity()->getMinPersonAnyRoom();
        $values[self::F_MAX_PERSON_ANY_ROOM] = $venue->getCapacity()->getMaxPersonAnyRoom();
        $values[self::F_MIN_ROOM_SIZE] = $venue->getCapacity()->getMinRoomSize();
        $values[self::F_MAX_ROOM_SIZE] = $venue->getCapacity()->getMaxRoomSize();
        $values[self::F_ROOM_COUNT] = $venue->getCapacity()->getRoomCount();
    }

    /**
     * @return int | null
     */
    protected function emptyToInt(string $value)
    {
        if ($value == "") {
            return null;
        }

        return (int) $value;
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }
}
