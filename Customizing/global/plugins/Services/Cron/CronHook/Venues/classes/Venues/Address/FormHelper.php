<?php

declare(strict_types=1);

namespace CaT\Plugins\Venues\Venues\Address;

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
    const F_ADDRESS1 = "address1";
    const F_COUNTRY = "country";
    const F_ADDRESS2 = "address2";
    const F_POSTCODE = "postcode";
    const F_CITY = "city";
    const F_LOCATION = "f_location";

    const ADDRESS1_LENGTH = 4;
    const CITY_LENGTH = 2;

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
        $sh->setTitle($this->txt("sh_address"));
        $form->addItem($sh);

        $ti = new \ilTextInputGUI($this->txt("address1"), self::F_ADDRESS1);
        $ti->setInfo(sprintf($this->txt("address1_info"), self::ADDRESS1_LENGTH));
        $ti->setRequired(true);
        $ti->setMaxLength(64);
        $form->addItem($ti);

        $ti = new \ilTextInputGUI(null, self::F_ADDRESS2);
        $ti->setMaxLength(64);
        $form->addItem($ti);

        $ti = new \ilTextInputGUI($this->txt("postcode"), self::F_POSTCODE);
        $form->addItem($ti);

        $ti = new \ilTextInputGUI($this->txt("city"), self::F_CITY);
        $ti->setInfo(sprintf($this->txt("city_info"), self::CITY_LENGTH));
        $ti->setMaxLength(64);
        $form->addItem($ti);

        $ti = new \ilTextInputGUI($this->txt("country"), self::F_COUNTRY);
        $form->addItem($ti);

        $loc = new \ilLocationInputGUI($this->txt("location"), self::F_LOCATION);
        $form->addItem($loc);
    }

    /**
     * @inheritdoc
     */
    public function createObject(int $venue_id, array $post)
    {
        $this->actions->createAddressObject(
            $venue_id,
            trim($post[self::F_ADDRESS1]),
            trim($post[self::F_COUNTRY]),
            trim($post[self::F_ADDRESS2]),
            trim($post[self::F_POSTCODE]),
            trim($post[self::F_CITY]),
            (float) trim($post[self::F_LOCATION]["latitude"]),
            (float) trim($post[self::F_LOCATION]["longitude"]),
            (int) trim($post[self::F_LOCATION]["zoom"])
        );
    }

    /**
     * @inheritdoc
     */
    public function getObject(int $venue_id, array $post)
    {
        return $this->actions->getAddressObject(
            $venue_id,
            trim($post[self::F_ADDRESS1]),
            trim($post[self::F_COUNTRY]),
            trim($post[self::F_ADDRESS2]),
            trim($post[self::F_POSTCODE]),
            trim($post[self::F_CITY]),
            (float) trim($post[self::F_LOCATION]["latitude"]),
            (float) trim($post[self::F_LOCATION]["longitude"]),
            (int) trim($post[self::F_LOCATION]["zoom"])
        );
    }

    /**
     * @inheritdoc
     */
    public function addValues(array &$values, Venue $venue)
    {
        $values[self::F_ADDRESS1] = $venue->getAddress()->getAddress1();
        $values[self::F_COUNTRY] = $venue->getAddress()->getCountry();
        $values[self::F_ADDRESS2] = $venue->getAddress()->getAddress2();
        $values[self::F_POSTCODE] = $venue->getAddress()->getPostcode();
        $values[self::F_CITY] = $venue->getAddress()->getCity();
        $values[self::F_LOCATION]["latitude"] = $venue->getAddress()->getLatitude();
        $values[self::F_LOCATION]["longitude"] = $venue->getAddress()->getLongitude();
        $values[self::F_LOCATION]["zoom"] = $venue->getAddress()->getZoom();
    }

    /**
     * @inheritdoc
     */
    public function checkValues(\ilPropertyFormGUI $form, array $post)
    {
        $ret = true;

        $address1 = trim($post[self::F_ADDRESS1]);
        if (strlen($address1) < self::ADDRESS1_LENGTH) {
            $gui = $form->getItemByPostVar(self::F_ADDRESS1);
            $gui->setAlert($this->txt("address1_short"));
            $ret = false;
        }

        $city = trim($post[self::F_CITY]);
        if ($city != "" && strlen($city) < self::CITY_LENGTH) {
            $gui = $form->getItemByPostVar(self::F_CITY);
            $gui->setAlert($this->txt("city_short"));
            $ret = false;
        }

        return $ret;
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }
}
