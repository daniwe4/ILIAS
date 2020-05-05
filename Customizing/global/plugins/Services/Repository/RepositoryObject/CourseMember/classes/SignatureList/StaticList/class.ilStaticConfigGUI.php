<?php

/**
 * GUI to view config the signature list
 *
 * @author Nils Haagen 	<nils.haagen@concepts-and-training.de>
 */
class ilStaticConfigGUI
{
    const CMD_SHOW = "cmd_show";
    const CMD_SAVE = "cmd_save";
    const F_IMAGE = "f_image";


    public function __construct(
        \ilCtrl $ctrl,
        \ilGlobalTemplateInterface $tpl,
        ilToolbarGUI $toolbar,
        CaT\Plugins\CourseMember\SignatureList\ilActions $actions
    ) {
        $this->g_ctrl = $ctrl;
        $this->g_tpl = $tpl;
        $this->g_toolbar = $toolbar;
        $this->actions = $actions;
    }


    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCmd();
        switch ($cmd) {
            case self::CMD_SHOW:
                $this->show();
                break;
            case self::CMD_SAVE:
                $this->save();
                break;
            default:
                throw new Exception("Unknown command: " . $cmd);
        }
    }

    /**
     * Show upload form
     *
     * @param ilPropertyFormGUI 	$form
     * @return void
     */
    public function show($form = null)
    {
        if ($form === null) {
            $form = $this->initForm();
        }
        $this->g_tpl->setContent($form->getHtml());
    }
    /**
     * Save form
     *
     * @param ilPropertyFormGUI 	$form
     * @return void
     */
    public function save()
    {
        $form = $this->initForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->show($form);
            return;
        }
        $inpt = $form->getItemByPostVar(self::F_IMAGE);

        if ($inpt->getDeletionFlag()) {
            $this->actions->delete();
            \ilUtil::sendSuccess($this->txt('siglist_img_deleted'));
        } else {
            $post = $_POST;
            $img = $post[self::F_IMAGE];

            if ($img['size'] > 0) {
                $this->actions->delete();
                if ($this->actions->upload($img)) {
                    \ilUtil::sendSuccess($this->txt('siglist_img_uploaded'));
                } else {
                    \ilUtil::sendFailure($this->txt('siglist_img_could_not_be_uploaded'));
                }
            }
        }
        $this->show();
    }


    /**
     * Init the form
     *
     * @return \ilPropertyFormGUI
     */
    protected function initForm()
    {
        require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        require_once "Services/Form/classes/class.ilImageFileInputGUI.php";

        $form = new \ilPropertyFormGUI();
        $form->setTitle($this->txt("siglist_config_form_title"));
        $form->setFormAction($this->g_ctrl->getFormAction($this));

        $file = new ilImageFileInputGUI($this->txt("siglist_image_upload"), self::F_IMAGE);
        $file->setSuffixes(array('png', 'jpg', 'jpeg', 'svg'));
        $file->setAllowDeletion(true);

        $img = $this->actions->getPath();
        if ($img) {
            $file->setImage($img);
        }

        $form->addItem($file, true);

        $form->addCommandButton(self::CMD_SAVE, $this->txt("save"));
        return $form;
    }


    /**
     * Translate var to text
     *
     * @param string 	$code
     *
     * @return $code
     */
    protected function txt($code)
    {
        return $this->actions->getPlugin()->txt($code);
    }
}
