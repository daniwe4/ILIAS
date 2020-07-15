<?php
/**
 * cat-tms-patch start
 */

namespace ILIAS\TMS\Booking;

use ILIAS\TMS\Wizard;
use ILIAS\TMS\Translations as TranslationDecorator;

require_once("Services/Form/classes/class.ilPropertyFormGUI.php");

/**
 * ILIAS Bindings for TMS-Booking process.
 *
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 */
class ILIASBindings implements Wizard\ILIASBindings
{
    /**
     * @var	ilCtrl
     */
    protected $ctrl;

    /**
     * @var object
     */
    protected $gui;

    /**
     * @var object
     */
    protected $parent_gui;

    /**
     * @var string
     */
    protected $parent_cmd;

    /**
     * @var \ILIAS\TMS\Translations
     */
    protected $translations;


    final public function __construct(\ilCtrl $ctrl, object $gui, object $parent_gui, string $parent_cmd, TranslationDecorator $translations)
    {
        $this->ctrl = $ctrl;
        $this->gui = $gui;
        $this->parent_gui = $parent_gui;
        $this->parent_cmd = $parent_cmd;
        $this->translations = $translations;
    }

    /**
     * @inheritdocs
     */
    public function getForm()
    {
        $form = new \ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this->gui));
        $form->setShowTopButtons(true);
        return $form;
    }


    /**
     * @inheritdoc
     */
    public function txt($id)
    {
        return $this->translations->getTxt($id);
    }

    /**
     * @inheritdoc
     */
    public function redirectToPreviousLocation($messages, $success)
    {
        if (count($messages)) {
            $message = join("<br/>", $messages);
            if ($success) {
                \ilUtil::sendSuccess($message, true);
            } else {
                \ilUtil::sendInfo($message, true);
            }
        }

        $tms_session = new \TMSSession();
        if ($tms_session->getCurrentSearch()) {
            $this->ctrl->redirect($this->parent_gui, $this->parent_cmd);
        } else {
            $this->ctrl->redirectToURL(\ilUserUtil::getStartingPointAsUrl());
        }
    }
}

/**
 * cat-tms-patch end
 */
