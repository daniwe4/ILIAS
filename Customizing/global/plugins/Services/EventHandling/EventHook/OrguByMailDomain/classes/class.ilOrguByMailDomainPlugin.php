<?php



require_once __DIR__ . '/class.DomainConfigurationGUI.php';
require_once __DIR__ . '/class.DomainConfigurationOverviewGUI.php';

use CaT\Plugins\OrguByMailDomain as OMD;

class ilOrguByMailDomainPlugin extends ilEventHookPlugin
{

    /**
     * Perform command if the event is thrown
     *
     * @inheritdoc
     */
    public function handleEvent($a_component, $a_event, $a_parameter)
    {
        if ($a_component === 'Services/User' && $a_event === 'afterCreate') {
            $this->handlerUserCreation($a_parameter['user_obj']);
        }
    }

    protected function handlerUserCreation(\ilObjUser $usr)
    {
        global $DIC;

        $config = $DIC['OrguByMailDomain.ConfigurationRepository']
                ->loadByTitle($this->extractEmailDomain($usr));
        if ($config === null) {
            return;
        }
        $orgus = $DIC['OrguByMailDomain.Orgus'];
        $orgu_ids = $config->getOrguIds();
        $position_id = $config->getPosition();
        $usr_id = (int) $usr->getId();
        if ($orgus->positionExists($position_id)) {
            foreach ($orgu_ids as $orgu_ref_id) {
                if ($orgus->orguExists($orgu_ref_id)) {
                    $orgus->assignUserToPositionAtOrgu($usr_id, $position_id, $orgu_ref_id);
                }
            }
        }
    }


    protected function extractEmailDomain(\ilObjUser $usr)
    {
        $mail = $usr->getEmail();
        return substr($mail, strrpos($mail, '@') + 1);
    }


    public function getPluginName()
    {
        return 'OrguByMailDomain';
    }

    protected function init()
    {
        parent::init();
        global $DIC;
        $self = $this;
        $DIC['OrguByMailDomain.plugin'] = function ($c) use ($self) {
            return $self;
        };
        $DIC['OrguByMailDomain.Orgus'] = function ($c) {
            return new OMD\IliasOrgus($c['ilDB']);
        };
        $DIC['OrguByMailDomain.ConfigurationRepository'] = function ($c) use ($self) {
            return new OMD\Configuration\Repository($c['ilDB']);
        };
        $DIC['OrguByMailDomain.DomainConfigurationOverviewGUI'] = function ($c) {
            return new DomainConfigurationOverviewGUI(
                $c['OrguByMailDomain.DomainConfigurationGUI'],
                $c['OrguByMailDomain.DomainConfigurationOverviewTableGUI'],
                $c['OrguByMailDomain.ConfigurationRepository'],
                $c['OrguByMailDomain.Orgus'],
                $c['OrguByMailDomain.plugin'],
                $c['ilCtrl'],
                $c['tpl']
            );
        };
        $DIC['OrguByMailDomain.DomainConfigurationGUI'] = function ($c) {
            return new DomainConfigurationGUI(
                $c['OrguByMailDomain.ConfigurationRepository'],
                $c['OrguByMailDomain.Orgus'],
                $c['OrguByMailDomain.plugin'],
                $c['ilCtrl'],
                $c['tpl']
            );
        };
        $DIC['OrguByMailDomain.DomainConfigurationOverviewTableGUI'] = function ($c) {
            return new OMD\DomainConfigurationOverviewTableGUI(
                $c['OrguByMailDomain.Orgus'],
                $c['OrguByMailDomain.ConfigurationRepository'],
                $c['OrguByMailDomain.plugin']
            );
        };
    }
}
