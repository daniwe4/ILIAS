<?php declare(strict_types=1);

namespace CaT\Plugins\OrguByMailDomain;

class DomainConfigurationOverviewTableGUI extends \ilTable2GUI
{
    protected $orgus;

    public function __construct(
        Orgus $orgus,
        Configuration\Repository $repository,
        \ilOrguByMailDomainPlugin $plugin
    ) {
        $this->orgus = $orgus;
        $this->plugin = $plugin;

        parent::__construct(null);

        $this->setEnableTitle(true);
        $this->setTitle($title);
        $this->setTopCommands(true);
        $this->setEnableHeader(true);
        $this->setExternalSorting(false);
        $this->setExternalSegmentation(false);
        $this->addCommandButton(
            \DomainConfigurationOverviewGUI::CMD_REQUEST_REMOVE_DOMAINS,
            $plugin->txt("delete_domains")
        );

        $this->setRowTemplate("tpl.domain_row.html", $this->plugin->getDirectory());

        $this->addColumn('', '');
        $this->addColumn($this->plugin->txt('domain'));
        $this->addColumn($this->plugin->txt('orgus'));
        $this->addColumn($this->plugin->txt('position'));
        $this->addColumn($this->plugin->txt('domain_description'));
        $this->addColumn($this->plugin->txt('actions'));

        $this->setData($this->createData($repository));
    }

    protected function createData(Configuration\Repository $repo)
    {
        $return = [];
        foreach ($repo->loadAll() as $obj) {
            $row = [];
            $domain_id = $obj->getId();
            $title = $obj->getTitle();
            $orgu_ids = $obj->getOrguIds();
            $position_id = $obj->getPosition();
            $desc = $obj->getDescription();

            $all_orgus = $this->orgus->orguList();

            $orgus = [];
            $orgu_warning = false;
            foreach ($orgu_ids as $orgu_ref_id) {
                if (!$this->orgus->orguExists($orgu_ref_id)) {
                    $orgu_warning = true;
                    continue;
                }
                $orgus[] = $all_orgus[$orgu_ref_id];
            }

            sort($orgus);
            $orgus_str = implode(',', $orgus);
            if ($orgu_warning) {
                $orgus_str .= $this->plugin->txt('orgu_warning');
            }
    
            $all_positions = $this->orgus->positionList();
            if (array_key_exists($position_id, $all_positions)) {
                $position = $all_positions[$position_id];
            } else {
                $position = $this->plugin->txt('position_warning');
            }

            $row['domain_id'] = $domain_id;
            $row['title'] = $title;
            $row['orgus'] = $orgus_str;
            $row['position'] = $position;
            $row['description'] = $desc;
            $return[] = $row;
        }
        return $return;
    }


    protected function actionMenuFor(int $domain_id)
    {
        $l = new \ilAdvancedSelectionListGUI();
        $l->setListTitle($this->plugin->txt("please_choose"));

        $this->ctrl->setParameterByClass(
            \DomainConfigurationGUI::class,
            \DomainConfigurationGUI::GET_DOMAIN_ID,
            $domain_id
        );
        $this->ctrl->setParameterByClass(
            \DomainConfigurationOverviewGUI::class,
            \DomainConfigurationOverviewGUI::GET_DOMAIN_ID,
            $domain_id
        );

        $l->addItem(
            $this->plugin->txt('edit'),
            'edit',
            $this->ctrl->getLinkTargetByClass(
                [\DomainConfigurationOverviewGUI::class,\DomainConfigurationGUI::class],
                \DomainConfigurationGUI::CMD_EDIT
            )
        );
        $l->addItem(
            $this->plugin->txt('delete_domain'),
            'remove',
            $this->ctrl->getLinkTarget(
                $this->parent_obj,
                \DomainConfigurationOverviewGUI::CMD_REQUEST_REMOVE_DOMAIN
            )
        );

        $this->ctrl->setParameterByClass(
            \DomainConfigurationGUI::class,
            \DomainConfigurationGUI::GET_DOMAIN_ID,
            null
        );
        $this->ctrl->setParameterByClass(
            \DomainConfigurationOverviewGUI::class,
            \DomainConfigurationOverviewGUI::GET_DOMAIN_ID,
            null
        );
        return $l->getHTML();
    }


    public function withParentObj(\DomainConfigurationOverviewGUI $gui)
    {
        $other = clone $this;
        $other->parent_obj = $gui;
        $other->setFormAction($this->ctrl->getFormAction($other->parent_obj));
        return $other;
    }

    public function withParentCmd(string $cmd)
    {
        $other = clone $this;
        $other->parent_cmd = $cmd;
        return $other;
    }

    protected function fillRow($set)
    {
        $this->tpl->setVariable('DOMAIN_ID', $set['domain_id']);
        $this->tpl->setVariable('DOMAIN', $set['title']);
        $this->tpl->setVariable('ORGUS', $set['orgus']);
        $this->tpl->setVariable('POSITION', $set['position']);
        $this->tpl->setVariable('DESCRIPTION', $set['description']);
        $this->tpl->setVariable('ACTIONS', $this->actionMenuFor($set['domain_id']));
    }
}
