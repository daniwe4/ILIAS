<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

declare(strict_types=1);

require_once(__DIR__ . "/../vendor/autoload.php");

use CaT\Plugins\Venues\DI;

/**
 * Configuration gui class of plugin.
 * Just forwarding to sub configuration classes.
 *
 * @ilCtrl_Calls ilVenuesConfigGUI: ilVenuesGUI
 * @ilCtrl_Calls ilVenuesConfigGUI: ilVenueTagsGUI, ilSearchTagsGUI
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilVenuesConfigGUI extends ilPluginConfigGUI
{
    use DI;
    const CMD_CONFIGURE = "configure";

    const TAB_VENUES = "venues";
    const TAB_TAGS_SEARCH = "tags_search";
    const TAB_TAGS_VENUE = "tags_venue";

    /**
     * @var ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var ilTabsGUI
     */
    protected $g_tabs;

    /**
     * @var ilActions
     */
    protected $actions;

    public function performCommand($cmd)
    {
        global $DIC;
        $this->dic = $this->getPluginDIC($this->plugin_object, $DIC);

        $this->actions = $this->plugin_object->getActions();
        $this->setTabs();

        $next_class = $this->dic["ilCtrl"]->getNextClass();

        switch ($next_class) {
            case "ilvenuesgui":
                $this->dic["ilTabs"]->activateTab(self::TAB_VENUES);
                $gui = $this->dic["config.venues.gui"];
                $this->dic["ilCtrl"]->forwardCommand($gui);
                break;
            case "ilvenuetagsgui":
                $this->dic["ilTabs"]->activateTab(self::TAB_TAGS_VENUE);
                $gui = $this->dic["config.tags.venue.gui"];
                $this->dic["ilCtrl"]->forwardCommand($gui);
                break;
            case "ilsearchtagsgui":
                $this->dic["ilTabs"]->activateTab(self::TAB_TAGS_SEARCH);
                $gui = $this->dic["config.tags.search.gui"];
                $this->dic["ilCtrl"]->forwardCommand($gui);
                break;
            default:
                switch ($cmd) {
                    case self::CMD_CONFIGURE:
                        $this->redirectVenuesGUI();
                        break;
                    default:
                        throw new Exception(__METHOD__ . ":: Unknown command: " . $cmd);
                }
        }
    }

    /**
     * Redirects to the venues main gui
     *
     * @return null
     */
    protected function redirectVenuesGUI()
    {
        ilUtil::redirect($this->dic["config.venues.link"]);
    }

    /**
     * Sets tabs for venues and tags
     *
     * @return null
     */
    protected function setTabs()
    {
        $this->dic["ilTabs"]->addTab(self::TAB_VENUES, $this->plugin_object->txt(self::TAB_VENUES), $this->dic["config.venues.link"]);
        $this->dic["ilTabs"]->addTab(self::TAB_TAGS_VENUE, $this->plugin_object->txt(self::TAB_TAGS_VENUE), $this->dic["config.tags.venue.link"]);
        $this->dic["ilTabs"]->addTab(self::TAB_TAGS_SEARCH, $this->plugin_object->txt(self::TAB_TAGS_SEARCH), $this->dic["config.tags.search.link"]);
    }
}
