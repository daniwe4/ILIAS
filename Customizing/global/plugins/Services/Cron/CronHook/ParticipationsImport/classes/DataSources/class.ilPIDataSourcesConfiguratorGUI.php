<?php declare(strict_types=1);

use CaT\Plugins\ParticipationsImport\DataSources\ConfigStorage;
use CaT\Plugins\ParticipationsImport\DataSources\Config;

class ilPIDataSourcesConfiguratorGUI
{
    const CMD_SHOW = 'show';
    const CMD_SAVE = 'save';

    const POST_EXTERN_CRS_ID_COL = 'extern_crs_id_col';
    const POST_CRS_TITLE_COL = 'crs_title_col_title';
    const POST_CRS_TYPE_COL = 'crs_type_col_title';
    const POST_CRS_BEGIN_DATE_COL = 'crs_begin_date_col_title';
    const POST_CRS_END_DATE_COL = 'crs_end_date_col_title';
    const POST_EXTERN_USR_ID_COL = 'extern_usr_id_col_title';
    const POST_PARTICIPATION_STATUS_COL = 'participation_status_col_title';
    const POST_BOOKING_STATUS_COL = 'booking_status_col_title';
    const POST_BOOKING_DATE_COL = 'booking_date_col_title';
    const POST_CRS_PROVIDER_COL = 'crs_provider_col_title';
    const POST_CRS_VENUE_COL = 'crs_venue_col_title';
    const POST_CRS_IDD_COL = 'crs_idd_col_title';
    const POST_PARTICIPATION_DATE_COL = 'participation_date_col_title';
    const POST_PARTICIPATION_IDD_COL = 'participation_idd_col_title';
    const POST_CRS_TYPE_DEFAULT = 'crs_type_default';
    const POST_CRS_TITLE_DEFAULT = 'crs_title_default';
    const POST_PARTICIPATION_STATUS_DEFAULT = 'participation_status_default';
    const POST_BOOKING_STATUS_DEFAULT = 'booking_status_default';
    const POST_IDD_DEFAULT_BOOKED = 'idd_default_booked';
    const POST_IDD_DEFAULT_ACHIEVED = 'idd_default_achieved';
    const POST_CRS_PROVIDER_DEFAULT = 'crs_provider_default';
    const POST_CRS_VENUE_DEFAULT = 'crs_venue_default';

    protected $plugin;
    protected $ctrl;
    protected $tpl;
    protected $tabs;
    protected $cs;

    public function __construct(
        \ilParticipationsImportPlugin $plugin,
        \ilCtrl $ctrl,
        \ilGlobalTemplateInterface $tpl,
        \ilTabsGUI $tabs,
        ConfigStorage $cs
    ) {
        $this->plugin = $plugin;
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->tabs = $tabs;
        $this->cs = $cs;
    }

    protected function txt($var)
    {
        return $this->plugin->txt($var);
    }

    public function executeCommand()
    {
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
        return true;
    }

    protected function show()
    {
        $form = $this->getForm();
        $config = $this->cs->loadCurrentConfig();
        $form->getItemByPostVar(self::POST_EXTERN_CRS_ID_COL)
            ->setValue($config->externCrsIdColTitle());
        $form->getItemByPostVar(self::POST_CRS_TITLE_COL)
            ->setValue($config->crsTitleColTitle());
        $form->getItemByPostVar(self::POST_CRS_TYPE_COL)
            ->setValue($config->crsTypeColTitle());
        $form->getItemByPostVar(self::POST_CRS_BEGIN_DATE_COL)
            ->setValue($config->crsBeginDateColTitle());
        $form->getItemByPostVar(self::POST_CRS_END_DATE_COL)
            ->setValue($config->crsEndDateColTitle());
        $form->getItemByPostVar(self::POST_CRS_PROVIDER_COL)
            ->setValue($config->crsProviderColTitle());
        $form->getItemByPostVar(self::POST_CRS_VENUE_COL)
            ->setValue($config->crsVenueColTitle());
        $form->getItemByPostVar(self::POST_CRS_IDD_COL)
            ->setValue($config->crsIddColTitle());
        $form->getItemByPostVar(self::POST_EXTERN_USR_ID_COL)
            ->setValue($config->externUsrIdColTitle());
        $form->getItemByPostVar(self::POST_PARTICIPATION_STATUS_COL)
            ->setValue($config->participationStatusColTitle());
        $form->getItemByPostVar(self::POST_BOOKING_STATUS_COL)
            ->setValue($config->bookingStatusColTitle());
        $form->getItemByPostVar(self::POST_BOOKING_DATE_COL)
            ->setValue($config->bookingDateColTitle());
        $form->getItemByPostVar(self::POST_PARTICIPATION_DATE_COL)
            ->setValue($config->participationDateColTitle());
        $form->getItemByPostVar(self::POST_PARTICIPATION_IDD_COL)
            ->setValue($config->participationIddColTitle());
        $form->getItemByPostVar(self::POST_CRS_TYPE_DEFAULT)
            ->setValue($config->crsTypeDefault());
        $form->getItemByPostVar(self::POST_CRS_TITLE_DEFAULT)
            ->setValue($config->crsTitleDefault());
        $form->getItemByPostVar(self::POST_PARTICIPATION_STATUS_DEFAULT)
            ->setValue($config->participationStatusDefault());
        $form->getItemByPostVar(self::POST_BOOKING_STATUS_DEFAULT)
            ->setValue($config->bookingStatusDefault());
        $form->getItemByPostVar(self::POST_IDD_DEFAULT_BOOKED)
            ->setValue($config->crsIddDefault() >= 0 ? $config->crsIddDefault() : '');
        $form->getItemByPostVar(self::POST_IDD_DEFAULT_ACHIEVED)
            ->setValue($config->participationIddDefault() >= 0 ? $config->participationIddDefault() : '');
        $form->getItemByPostVar(self::POST_CRS_PROVIDER_DEFAULT)
            ->setValue($config->crsProviderDefault());
        $form->getItemByPostVar(self::POST_CRS_VENUE_DEFAULT)
            ->setValue($config->crsVenueDefault());
        $this->tpl->setContent($form->getHTML());
    }

    protected function save()
    {
        $form = $this->getForm();
        $form->setValuesByPost();
        if ($form->checkInput() && $this->checkFormEntries($form)) {
            $this->cs->storeConfigAsCurrent(
                new Config(
                    trim((string) $form->getItemByPostVar(self::POST_EXTERN_CRS_ID_COL)->getValue()),
                    trim((string) $form->getItemByPostVar(self::POST_CRS_TITLE_COL)->getValue()),
                    trim((string) $form->getItemByPostVar(self::POST_CRS_TYPE_COL)->getValue()),
                    trim((string) $form->getItemByPostVar(self::POST_CRS_BEGIN_DATE_COL)->getValue()),
                    trim((string) $form->getItemByPostVar(self::POST_CRS_END_DATE_COL)->getValue()),
                    trim((string) $form->getItemByPostVar(self::POST_CRS_PROVIDER_COL)->getValue()),
                    trim((string) $form->getItemByPostVar(self::POST_CRS_VENUE_COL)->getValue()),
                    trim((string) $form->getItemByPostVar(self::POST_CRS_IDD_COL)->getValue()),
                    trim((string) $form->getItemByPostVar(self::POST_EXTERN_USR_ID_COL)->getValue()),
                    trim((string) $form->getItemByPostVar(self::POST_PARTICIPATION_STATUS_COL)->getValue()),
                    trim((string) $form->getItemByPostVar(self::POST_BOOKING_STATUS_COL)->getValue()),
                    trim((string) $form->getItemByPostVar(self::POST_BOOKING_DATE_COL)->getValue()),
                    trim((string) $form->getItemByPostVar(self::POST_PARTICIPATION_DATE_COL)->getValue()),
                    trim((string) $form->getItemByPostVar(self::POST_PARTICIPATION_IDD_COL)->getValue()),
                    trim((string) $form->getItemByPostVar(self::POST_CRS_TYPE_DEFAULT)->getValue()),
                    trim((string) $form->getItemByPostVar(self::POST_CRS_TITLE_DEFAULT)->getValue()),
                    trim((string) $form->getItemByPostVar(self::POST_PARTICIPATION_STATUS_DEFAULT)->getValue()),
                    trim((string) $form->getItemByPostVar(self::POST_BOOKING_STATUS_DEFAULT)->getValue()),
                    trim($form->getItemByPostVar(self::POST_IDD_DEFAULT_BOOKED)->getValue()) !== '' ?
                        (int) $form->getItemByPostVar(self::POST_IDD_DEFAULT_BOOKED)->getValue() :
                        Config::NONE_INT,
                    trim($form->getItemByPostVar(self::POST_IDD_DEFAULT_ACHIEVED)->getValue()) !== '' ?
                        (int) $form->getItemByPostVar(self::POST_IDD_DEFAULT_ACHIEVED)->getValue() :
                        Config::NONE_INT,
                    trim((string) $form->getItemByPostVar(self::POST_CRS_PROVIDER_DEFAULT)->getValue()),
                    trim((string) $form->getItemByPostVar(self::POST_CRS_VENUE_DEFAULT)->getValue())
                )
            );
            $this->show();
        } else {
            $this->tpl->setContent($form->getHTML());
        }
    }


    protected function checkFormEntries(\ilPropertyFormGUI $form) : bool
    {
        $ok = true;
        $idd_booked = trim($form->getItemByPostVar(self::POST_IDD_DEFAULT_BOOKED)->getValue());
        if ($idd_booked !== '' && 1 !== preg_match('#^[0-9]*$#', $idd_booked)) {
            $ok = false;
            \ilUtil::sendFailure($this->txt('invalid_input_idd_default_booked'));
        }
        $idd_achived = trim($form->getItemByPostVar(self::POST_IDD_DEFAULT_ACHIEVED)->getValue());
        if ($idd_achived !== '' && 1 !== preg_match('#^[0-9]*$#', $idd_achived)) {
            $ok = false;
            \ilUtil::sendFailure($this->txt('invalid_input_idd_default_achived'));
        }
        return $ok;
    }

    protected function getForm() : \ilPropertyFormGUI
    {
        $form = new \ilPropertyFormGUI();
        $form->setTitle($this->txt("import_file_config"));
        $form->setFormAction($this->ctrl->getFormAction($this));

        $sec_l = new \ilFormSectionHeaderGUI();
        $sec_l->setTitle($this->txt("course_data_info"));
        $form->addItem($sec_l);

        $msi = new \ilTextInputGUI($this->txt("extern_crs_id_col"), self::POST_EXTERN_CRS_ID_COL);
        $msi->setRequired(true);
        $msi->setInfo($this->txt("extern_crs_id_col_title_info"));
        $form->addItem($msi);

        $msi = new \ilTextInputGUI($this->txt("crs_title_col_title"), self::POST_CRS_TITLE_COL);
        $form->addItem($msi);

        $msi = new \ilTextInputGUI($this->txt("crs_type_col_title"), self::POST_CRS_TYPE_COL);
        $form->addItem($msi);

        $msi = new \ilTextInputGUI($this->txt("crs_begin_date_col_title"), self::POST_CRS_BEGIN_DATE_COL);
        $msi->setInfo($this->txt("crs_begin_date_col_title_info"));
        $form->addItem($msi);

        $msi = new \ilTextInputGUI($this->txt("crs_end_date_col_title"), self::POST_CRS_END_DATE_COL);
        $msi->setInfo($this->txt("crs_end_date_col_title_info"));
        $form->addItem($msi);

        $msi = new \ilTextInputGUI($this->txt("crs_provider_col_title"), self::POST_CRS_PROVIDER_COL);
        $form->addItem($msi);

        $msi = new \ilTextInputGUI($this->txt("crs_venue_col_title"), self::POST_CRS_VENUE_COL);
        $form->addItem($msi);

        $msi = new \ilTextInputGUI($this->txt("crs_idd_col_title"), self::POST_CRS_IDD_COL);
        $form->addItem($msi);

        $sec_l = new \ilFormSectionHeaderGUI();
        $sec_l->setTitle($this->txt("participation_data_info"));
        $form->addItem($sec_l);

        $msi = new \ilTextInputGUI($this->txt("extern_usr_id_col_title"), self::POST_EXTERN_USR_ID_COL);
        $msi->setRequired(true);
        $msi->setInfo($this->txt("extern_usr_id_col_title_info"));
        $form->addItem($msi);

        $msi = new \ilTextInputGUI($this->txt("participation_status_col_title"), self::POST_PARTICIPATION_STATUS_COL);
        $form->addItem($msi);

        $msi = new \ilTextInputGUI($this->txt("booking_status_col_title"), self::POST_BOOKING_STATUS_COL);
        $form->addItem($msi);

        $msi = new \ilTextInputGUI($this->txt("booking_date_col_title"), self::POST_BOOKING_DATE_COL);
        $msi->setInfo($this->txt("booking_date_col_title_info"));
        $form->addItem($msi);

        $msi = new \ilTextInputGUI($this->txt("participation_date_col_title"), self::POST_PARTICIPATION_DATE_COL);
        $msi->setInfo($this->txt("participation_date_col_title_info"));
        $form->addItem($msi);

        $msi = new \ilTextInputGUI($this->txt("partricipation_idd_col_title"), self::POST_PARTICIPATION_IDD_COL);
        $form->addItem($msi);

        $sec_l = new \ilFormSectionHeaderGUI();
        $sec_l->setTitle($this->txt("default_data_info"));
        $form->addItem($sec_l);

        $msi = new \ilTextInputGUI($this->txt("crs_type_default"), self::POST_CRS_TYPE_DEFAULT);
        $msi->setInfo($this->txt("crs_type_default_info"));
        $form->addItem($msi);

        $msi = new \ilTextInputGUI($this->txt("crs_title_default"), self::POST_CRS_TITLE_DEFAULT);
        $msi->setInfo($this->txt("crs_title_default_info"));
        $form->addItem($msi);

        $msi = new \ilTextInputGUI($this->txt("participation_status_default"), self::POST_PARTICIPATION_STATUS_DEFAULT);
        $msi->setInfo($this->txt("participation_status_default_info"));
        $form->addItem($msi);

        $msi = new \ilTextInputGUI($this->txt("booking_status_default"), self::POST_BOOKING_STATUS_DEFAULT);
        $msi->setInfo($this->txt("booking_status_default_info"));
        $form->addItem($msi);

        $msi = new \ilTextInputGUI($this->txt("idd_default_booked"), self::POST_IDD_DEFAULT_BOOKED);
        $msi->setInfo($this->txt("idd_default_booked_info"));
        $form->addItem($msi);

        $msi = new \ilTextInputGUI($this->txt("idd_default_achieved"), self::POST_IDD_DEFAULT_ACHIEVED);
        $msi->setInfo($this->txt("idd_default_achieved_info"));
        $form->addItem($msi);

        $msi = new \ilTextInputGUI($this->txt("crs_provider_default"), self::POST_CRS_PROVIDER_DEFAULT);
        $msi->setInfo($this->txt("crs_provider_defaul_info"));
        $form->addItem($msi);

        $msi = new \ilTextInputGUI($this->txt("crs_venue_default"), self::POST_CRS_VENUE_DEFAULT);
        $msi->setInfo($this->txt("crs_venue_default_info"));
        $form->addItem($msi);

        $form->addCommandButton(self::CMD_SAVE, $this->txt("save"));
        return $form;
    }
}
