<?php
use CaT\Plugins\CronJobSurveillance\Config\FormBuilder;
use CaT\Plugins\CronJobSurveillance\Config\ConfigurationForm;
use CaT\Plugins\CronJobSurveillance\Config\CJSConfigGUI;
use CaT\Plugins\CronJobSurveillance\Config\DB;

/**
 * GUI class for setting cron jobs under surveillance.
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class ilCJSConfigGUI extends CJSConfigGUI
{
    public function __construct(
        FormBuilder $fb,
        ConfigurationForm $cf,
        DB $db,
        \Closure $txt
    ) {
        global $DIC;

        $this->g_ctrl = $DIC->ctrl();
        $this->g_tpl = $DIC->ui()->mainTemplate();
        $this->g_tpl->addJavaScript("Customizing/global/plugins/Services/Cron/CronHook/CronJobSurveillance/classes/Config/main.js");

        parent::__construct($fb, $cf, $db, $txt);
    }

    /**
     * @inheritdoc
     */
    protected function getCommand()
    {
        return $this->g_ctrl->getCmd();
    }

    /**
     * @inheritdoc
     */
    protected function setContent($html)
    {
        $this->g_tpl->setContent($html);
    }

    /**
     * @inheritdoc
     */
    protected function getPost()
    {
        $post = $_POST;

        if (is_array($post)) {
            return $post;
        }

        return array();
    }
}
