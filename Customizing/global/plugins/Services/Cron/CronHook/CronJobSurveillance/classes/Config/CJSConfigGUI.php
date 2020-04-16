<?php
namespace CaT\Plugins\CronJobSurveillance\Config;

/**
 * GUI class for setting cron jobs under surveillance.
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
abstract class CJSConfigGUI
{
    const CMD_SHOW_CONFIG = "showConfig";
    const CMD_SAVE_CONFIG = "saveConfig";
    const CMD_CANCEL_CONFIG = "cancelConfig";
    const CMD_DELETE_ALL = "deleteAll";

    /**
     * @var FormBuilder
     */
    protected $form_builder;

    /**
     * @var ConfigurationForm
     */
    protected $configuration_form;

    /**
     * @var DB
     */
    protected $db;

    /**
     * @var IliasFormWrapper
     */
    protected $form;

    public function __construct(
        FormBuilder $form_builder,
        ConfigurationForm $configuration_form,
        DB $db,
        \Closure $txt
    ) {
        $this->form_builder = $form_builder;
        $this->configuration_form = $configuration_form;
        $this->db = $db;
        $this->txt = $txt;
        $this->form = null;
    }

    /**
     * Process incomming commands.
     *
     * @param 	string 	$command
     * @return 	void
     */
    public function executeCommand()
    {
        $command = $this->getCommand();

        switch ($command) {
            case self::CMD_SHOW_CONFIG:
                $this->show();
                break;
            case self::CMD_SAVE_CONFIG:
                $this->save();
                break;
            case self::CMD_CANCEL_CONFIG:
                $this->cancel();
                break;
            case self::CMD_DELETE_ALL:
                $this->deleteAll();
                break;
            default:
                throw new \Exception("Unknown command '$command'.");
        }
    }

    /**
     * Display the gui.
     *
     * @return void
     */
    public function show($form = null)
    {
        if ($form == null) {
            $form = $this->getForm();
            $result = $this->db->select();
            $form->setInputByArray($result);
        }

        $this->setContent($form->getHtml());
    }

    /**
     * Save user input.
     *
     * @return void
     */
    public function save()
    {
        $form = $this->getForm();
        $check = $form->checkInput();
        $job_settings = $this->configuration_form->genrateJobSettingsArray($this->getPost());

        if (!$check) {
            $form->setInputByArray($job_settings);
            \ilUtil::sendFailure($this->txt("wrong_input_warning"));
            $this->show($form);
            return;
        }

        $this->db->deleteAll();

        foreach ($job_settings as $key => $job_setting) {
            $this->db->create($job_setting, $key);
        }

        $this->show();
    }

    /**
     * Cancel the user input return to show.
     *
     * @return void
     */
    public function cancel()
    {
        $this->show();
    }

    /**
     * Delete all CronJobs under surveillance.
     *
     * @return void
     */
    public function deleteAll()
    {
        $this->db->deleteAll();
        \ilUtil::sendSuccess($this->txt("all_jobs_deleted"));
        $this->show();
    }

    /**
     * Get a instance implementing a FormWrapper.
     *
     * @return FormWrapper
     */
    public function getForm()
    {
        if ($this->form == null) {
            $this->form = $this->configuration_form->build($this->form_builder);
        }
        return $this->form;
    }

    /**
     * Translate code to lang value
     *
     * @param 	string 	$code
     * @return 	string
     */
    protected function txt($code)
    {
        assert('is_string($code)');

        $txt = $this->txt;

        return $txt($code);
    }

    /**
     * Get post array.
     *
     * @return 	array
     */
    abstract protected function getPost();

    /**
     * Returns the actual command.
     *
     * @return string
     */
    abstract protected function getCommand();

    /**
     * Set the content of our form.
     *
     * @return void
     */
    abstract protected function setContent($html);
}
