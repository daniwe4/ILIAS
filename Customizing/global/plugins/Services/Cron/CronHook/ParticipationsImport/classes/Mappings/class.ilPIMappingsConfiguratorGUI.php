<?php declare(strict_types=1);

use CaT\Plugins\ParticipationsImport\IliasUtils\UserUtils;
use CaT\Plugins\ParticipationsImport\Mappings\Config;
use CaT\Plugins\ParticipationsImport\Mappings\ConfigStorage;

/**
 * @ilCtrl_Calls ilPIMappingsConfiguratorGUI: ilPIParticipationMappingsConfiguratorGUI
 * @ilCtrl_Calls ilPIMappingsConfiguratorGUI: ilPIBookingMappingsConfiguratorGUI
 */
class ilPIMappingsConfiguratorGUI
{
    protected $plugin;
    protected $ctrl;
    protected $tpl;
    protected $tabs;
    protected $uu;
    protected $cs;


    const CMD_SHOW = 'show_user_mapping';
    const CMD_SAVE = 'save_user_mapping';

    const POST_FIELD_USER_MAPPING = 'user_mapping';

    public function __construct(
        \ilParticipationsImportPlugin $plugin,
        \ilCtrl $ctrl,
        \ilTemplate $tpl,
        \ilTabsGUI $tabs,
        UserUtils $uu,
        ConfigStorage $cs,
        ilPIBookingMappingsConfiguratorGUI $booking_mapping,
        ilPIParticipationMappingsConfiguratorGUI $participation_mapping
    ) {
        $this->plugin = $plugin;
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->tabs = $tabs;
        $this->uu = $uu;
        $this->cs = $cs;
        $this->booking_mapping = $booking_mapping->withParent($this);
        $this->participation_mapping = $participation_mapping->withParent($this);
    }

    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass();
        $this->configureSubTabs();
        switch ($next_class) {
            case 'ilpibookingmappingsconfiguratorgui':
                $this->bookingMapping();
                break;
            case 'ilpiparticipationmappingsconfiguratorgui':
                $this->participationMapping();
                break;
            default:
                $cmd = $this->ctrl->getCmd();
                switch ($cmd) {
                    case self::CMD_SHOW:
                        $this->show();
                        break;
                    case self::CMD_SAVE:
                        $this->save();
                        break;
                    default:
                        throw new ilException("no command or next class given");
                }
        }
        return true;
    }

    protected function configureSubTabs()
    {
        $this->tabs->addSubTab(
            self::CMD_SHOW,
            $this->txt(self::CMD_SHOW),
            $this->ctrl->getLinkTarget($this, self::CMD_SHOW)
        );
        $this->tabs->addSubTab(
            'booking_status_mapping',
            $this->txt('booking_status_mapping'),
            $this->ctrl->getLinkTargetByClass(
                [self::class,ilPIBookingMappingsConfiguratorGUI::class],
                ilPIStatusMappingConfiguratorGUI::CMD_SHOW
            )
        );
        $this->tabs->addSubTab(
            'participation_status_mapping',
            $this->txt('participation_status_mapping'),
            $this->ctrl->getLinkTargetByClass(
                [self::class,ilPIParticipationMappingsConfiguratorGUI::class],
                ilPIStatusMappingConfiguratorGUI::CMD_SHOW
            )
        );
    }

    protected function txt($var)
    {
        return $this->plugin->txt($var);
    }

    protected function show()
    {
        $this->tabs->activateSubTab(self::CMD_SHOW);
        $form = $this->getUserMappingsConfigForm();
        $config = $this->cs->loadCurrentConfig();
        $form->getItemByPostVar(self::POST_FIELD_USER_MAPPING)
            ->setValue($config->externUsrIdField());
        $this->tpl->setContent($form->getHTML());
    }

    protected function save()
    {
        $form = $this->getUserMappingsConfigForm();
        $form->setValuesByPost();
        $field = (int) $form
                    ->getItemByPostVar(self::POST_FIELD_USER_MAPPING)
                    ->getValue();
        if ($field === Config::NONE || !$field) {
            \ilUtil::sendFailure($this->txt('invalid_field_to_identify_user'));
            $this->tpl->setContent($form->getHTML());
        } else {
            $this->cs->storeConfigAsCurrent(new Config($field));
            $this->show();
        }
    }

    protected function getUserMappingsConfigForm() : \ilPropertyFormGUI
    {
        $form = new \ilPropertyFormGUI();
        $form->setTitle($this->txt("extern_user_id_field"));
        $form->setFormAction($this->ctrl->getFormAction($this));

        $options = $this->uu->udfFields();
        asort($options);
        $options = [
                Config::NONE => '-',
                Config::LOGIN => $this->txt("field_login"),
                Config::EMAIL => $this->txt("field_email")
                ] + $options;
        $msi = new \ilSelectInputGUI($this->txt("field_select"), self::POST_FIELD_USER_MAPPING);
        $msi->setOptions($options);
        $msi->setRequired(true);
        $msi->setInfo($this->txt("extern_user_id_field_info"));
        $form->addItem($msi);
        $form->addCommandButton(self::CMD_SAVE, $this->txt("save"));
        return $form;
    }

    protected function bookingMapping()
    {
        $this->tabs->activateSubTab('booking_status_mapping');
        $this->ctrl->forwardCommand(
            $this->booking_mapping
        );
    }

    protected function participationMapping()
    {
        $this->tabs->activateSubTab('participation_status_mapping');
        $this->ctrl->forwardCommand(
            $this->participation_mapping
        );
    }
}
