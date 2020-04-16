<?php

declare(strict_types=1);

namespace CaT\Plugins\Venues\Venues\Conditions;

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
    const F_GENERAL_AGREEMENT = "generalAgreement";
    const F_TERMS = "terms";
    const F_VALUTA = "valuta";

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
        $sh->setTitle($this->txt("sh_terms"));
        $form->addItem($sh);

        $cb = new \ilCheckboxInputGUI($this->txt("general_agreement"), self::F_GENERAL_AGREEMENT);
        $form->addItem($cb);

        $ta = new \ilTextareaInputGUI($this->txt("terms"), self::F_TERMS);
        $form->addItem($ta);

        $ti = new \ilTextInputGUI($this->txt("valuta"), self::F_VALUTA);
        $ti->setMaxLength(32);
        $form->addItem($ti);
    }

    /**
     * @inheritdoc
     */
    public function createObject(int $venue_id, array $post)
    {
        $general_agreement = false;
        if (isset($post[self::F_GENERAL_AGREEMENT]) && $post[self::F_GENERAL_AGREEMENT] == "1") {
            $general_agreement = true;
        }

        $this->actions->createConditionsObject(
            $venue_id,
            $general_agreement,
            trim($post[self::F_TERMS]),
            trim($post[self::F_VALUTA])
        );
    }

    /**
     * @inheritdoc
     */
    public function getObject(int $venue_id, array $post)
    {
        $general_agreement = false;
        if (isset($post[self::F_GENERAL_AGREEMENT]) && $post[self::F_GENERAL_AGREEMENT] == "1") {
            $general_agreement = true;
        }

        return $this->actions->getConditionsObject(
            $venue_id,
            $general_agreement,
            trim($post[self::F_TERMS]),
            trim($post[self::F_VALUTA])
        );
    }

    /**
     * @inheritdoc
     */
    public function addValues(array &$values, Venue $venue)
    {
        $values[self::F_GENERAL_AGREEMENT] = $venue->getCondition()->getGeneralAgreement();
        $values[self::F_TERMS] = $venue->getCondition()->getTerms();
        $values[self::F_VALUTA] = $venue->getCondition()->getValuta();
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }
}
