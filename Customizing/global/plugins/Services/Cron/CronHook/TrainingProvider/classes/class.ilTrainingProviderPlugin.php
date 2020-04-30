<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Cron/classes/class.ilCronHookPlugin.php");


use \CaT\Plugins\TrainingProvider;

/**
 * Plugin base class. Keeps all information the plugin needs
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilTrainingProviderPlugin extends ilCronHookPlugin
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get the name of the Plugin
     *
     * @return string
     */
    public function getPluginName()
    {
        return "TrainingProvider";
    }

    /**
     * Get an array with 1 to n numbers of cronjob objects
     *
     * @return ilJob[]
     */
    public function getCronJobInstances()
    {
    }

    /**
     * Get a single cronjob object
     *
     * @return ilJob
     */
    public function getCronJobInstance($a_job_id)
    {
    }

    /**
     * Get a closure to get txts from plugin.
     *
     * @return \Closure
     */
    public function txtClosure()
    {
        return function ($code) {
            return $this->txt($code);
        };
    }

    public function getActions()
    {
        if ($this->actions === null) {
            global $DIC;
            $db = $DIC->database();

            $this->actions = new TrainingProvider\ilActions(
                $this->getProviderDB($db),
                $this->getTrainerDB($db),
                $this->getTagsDB($db),
                $this->getAssingmentDB($db),
                $DIC["ilAppEventHandler"]
            );
        }

        return $this->actions;
    }

    protected function getProviderDB($db)
    {
        if ($this->provider_db === null) {
            $this->provider_db = new TrainingProvider\Provider\ilDB($db);
        }

        return $this->provider_db;
    }

    protected function getTrainerDB($db)
    {
        if ($this->trainer_db === null) {
            $this->trainer_db = new TrainingProvider\Trainer\ilDB($db);
        }

        return $this->trainer_db;
    }

    protected function getTagsDB($db)
    {
        if ($this->tags_db === null) {
            $this->tags_db = new TrainingProvider\Tags\ilDB($db);
        }

        return $this->tags_db;
    }

    /**
     * Get DB interface for provider/course assignment
     *
     * @param $db
     *
     * @return TrainingProvider\ProviderAssignement\DB
     */
    protected function getAssingmentDB($db)
    {
        if ($this->assign_db === null) {
            $this->assign_db = new TrainingProvider\ProviderAssignment\ilDB($db);
        }
        return $this->assign_db;
    }

    /**
     * Get information about selected provider
     *
     * @param int 	$crs_id
     *
     * @return string[]
     */
    public function getProviderInfos($crs_id)
    {
        $pactions = $this->getActions();
        $passignment = $pactions->getAssignment((int) $crs_id);
        $provider_id = -1;
        $custom_assignment = false;
        $provider = "";

        if ($passignment) {
            if ($passignment->isListAssignment()) {
                $provider_id = $passignment->getProviderId();
                $provider = $pactions->getCurrentProviderName($provider_id);
            } else {
                $provider = $passignment->getProviderText();
                $custom_assignment = true;
            }
        }

        return array($provider_id, $provider, $custom_assignment);
    }
}
