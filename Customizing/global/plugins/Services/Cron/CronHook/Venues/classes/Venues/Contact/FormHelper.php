<?php

declare(strict_types=1);

namespace CaT\Plugins\Venues\Venues\Contact;

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
    const F_INTERNAL_CONTACT = "internalContact";
    const F_CONTACT = "contact";
    const F_PHONE = "phone";
    const F_FAX = "fax";
    const F_EMAIL = "email";

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
        $sh->setTitle($this->txt("sh_contact"));
        $form->addItem($sh);

        $ti = new \ilTextInputGUI($this->txt("internal_contact"), self::F_INTERNAL_CONTACT);
        $ti->setMaxLength(128);
        $form->addItem($ti);

        $ti = new \ilTextInputGUI($this->txt("contact"), self::F_CONTACT);
        $ti->setMaxLength(128);
        $form->addItem($ti);

        $ti = new \ilTextInputGUI($this->txt("phone"), self::F_PHONE);
        $ti->setMaxLength(32);
        $form->addItem($ti);

        $ti = new \ilTextInputGUI($this->txt("fax"), self::F_FAX);
        $ti->setMaxLength(32);
        $form->addItem($ti);

        $ti = new \ilEMailInputGUI($this->txt("email"), self::F_EMAIL);
        $ti->setSize(40);
        $ti->setMaxLength(128);
        $form->addItem($ti);
    }

    /**
     * @inheritdoc
     */
    public function createObject(int $venue_id, array $post)
    {
        $this->actions->createContactObject(
            $venue_id,
            trim($post[self::F_INTERNAL_CONTACT]),
            trim($post[self::F_CONTACT]),
            trim($post[self::F_PHONE]),
            trim($post[self::F_FAX]),
            trim($post[self::F_EMAIL])
        );
    }

    /**
     * @inheritdoc
     */
    public function getObject(int $venue_id, array $post)
    {
        return $this->actions->getContactObject(
            $venue_id,
            trim($post[self::F_INTERNAL_CONTACT]),
            trim($post[self::F_CONTACT]),
            trim($post[self::F_PHONE]),
            trim($post[self::F_FAX]),
            trim($post[self::F_EMAIL])
        );
    }

    /**
     * @inheritdoc
     */
    public function addValues(array &$values, Venue $venue)
    {
        $values[self::F_INTERNAL_CONTACT] = $venue->getContact()->getInternalContact();
        $values[self::F_CONTACT] = $venue->getContact()->getContact();
        $values[self::F_PHONE] = $venue->getContact()->getPhone();
        $values[self::F_FAX] = $venue->getContact()->getFax();
        $values[self::F_EMAIL] = $venue->getContact()->getEmail();
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }
}
