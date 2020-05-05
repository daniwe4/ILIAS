<?php

declare(strict_types=1);

use CaT\Plugins\UserBookings\ilObjActions;
use CaT\Plugins\UserBookings\UserBooking;
use CaT\Plugins\UserBookings\Helper;

/**
* @ilCtrl_Calls ilUserBookingsGUI: ilTMSSelfCancelGUI, ilTMSSelfCancelWaitingGUI
*/
class ilUserBookingsGUI
{
    const CMD_SHOW_BOOKINGS = "showBookings";
    const HITS_PER_PAGE = "hits_per_page";
    const PAGINATION_PARAM = "pagination";
    const DROPDOWN_AT_PAGES = 1;

    /**
     * @var ilObjUserBookingsGUI
     */
    protected $parent;

    /**
     * @var ilObjActions
     */
    protected $actions;

    /**
     * @var ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $g_tpl;

    /**
     * @var ilAccess
     */
    protected $g_access;

    /**
     * @var ilObjUser
     */
    protected $g_user;

    public function __construct(ilObjUserBookingsGUI $parent, ilObjActions $actions, Helper $helper)
    {
        $this->parent = $parent;
        $this->actions = $actions;
        $this->helper = $helper;

        global $DIC;
        $this->g_ctrl = $DIC->ctrl();
        $this->g_tpl = $DIC->ui()->mainTemplate();
        $this->g_user = $DIC->user();
        $this->g_tabs = $DIC->tabs();
        $this->g_access = $DIC->access();
        $this->locator = $DIC['ilLocator'];
        $this->g_f = $DIC->ui()->factory();

        $this->g_tabs->clearTargets();
    }

    public function executeCommand()
    {
        $next_class = $this->g_ctrl->getNextClass();
        $cmd = $this->g_ctrl->getCmd();

        switch ($next_class) {
            case "iltmsselfcancelgui":
                require_once("Services/TMS/Cancel/classes/class.ilTMSSelfCancelGUI.php");
                $gui = new ilTMSSelfCancelGUI($this, self::CMD_SHOW_BOOKINGS, false);
                $this->g_ctrl->forwardCommand($gui);
                break;
            case "iltmsselfcancelwaitinggui":
                require_once("Services/TMS/Cancel/classes/class.ilTMSSelfCancelWaitingGUI.php");
                $gui = new ilTMSSelfCancelWaitingGUI($this, self::CMD_SHOW_BOOKINGS, false);
                $this->g_ctrl->forwardCommand($gui);
                break;
            default:
                switch ($cmd) {
                    case self::CMD_SHOW_BOOKINGS:
                        if ($this->g_access->checkAccess("read", "", $this->parent->object->getRefId())) {
                            $this->showBookings();
                        } else {
                            \ilUtil::redirect("");
                        }
                        break;
                    default:
                        throw new Exception("Uknown command: " . $cmd);
                }
        }
    }

    /**
     * Lists all bookings as table
     *
     * @return void
     */
    protected function showBookings()
    {
        $data = $this->actions->getBookedTrainingOf((int) $this->g_user->getId());
        $data = $this->sortData($data);
        $pagination = $this->getPagination(count($data));

        $view_controls = [];
        if ($pagination->getNumberOfPages() > 1) {
            $view_controls[] = $pagination;
        }

        $table = new UserBooking\ilUserBookingsTableGUI($this, $this->helper, $this->g_user->getId(), $view_controls);
        $table->setData($data);

        $offset = $pagination->getOffset();
        $limit = $pagination->getPageSize();
        $content = $table->getHtml($offset, $limit, $this->actions->isReccomendationAllowed());
        if (count($data) == 0) {
            $content .= $this->getNoAvailableTrainings();
        }

        $this->g_tpl->setContent($content);
    }

    /**
     * @param UserBooking\UserBooking[]
     * @return UserBooking\UserBooking[]
     */
    protected function sortData(array $data) : array
    {
        uasort(
            $data,
            function (UserBooking\UserBooking $a, UserBooking\UserBooking $b) {
                if (is_null($a->getBeginDate()) && is_null($b->getBeginDate())) {
                    return 0;
                }

                if (!is_null($a->getBeginDate()) && is_null($b->getBeginDate())) {
                    return -1;
                }

                if (is_null($a->getBeginDate()) && !is_null($b->getBeginDate())) {
                    return 1;
                }

                return strcmp($a->getBeginDate()->get(IL_CAL_DATE), $b->getBeginDate()->get(IL_CAL_DATE));
            }
        );
        return $data;
    }

    protected function getPagination(int $max_number) : \ILIAS\UI\Implementation\Component\ViewControl\Pagination
    {
        $current_page = (int) $_GET[self::PAGINATION_PARAM];
        $link = $this->g_ctrl->getLinkTarget($this, self::CMD_SHOW_BOOKINGS, "", false, false);
        $limit = (int) $this->g_user->getPref(self::HITS_PER_PAGE);

        return $this->g_f->viewControl()->pagination()
            ->withTotalEntries($max_number)
            ->withPageSize($limit)
            ->withCurrentPage($current_page)
            ->withTargetURL($link, self::PAGINATION_PARAM)
            ->withDropdownAt(self::DROPDOWN_AT_PAGES);
    }

    /**
     * Get empty search-results message
     *
     * @return void
     */
    protected function getNoAvailableTrainings()
    {
        return $this->txt('no_trainings_available');
    }

    public function txt($cmd)
    {
        return $this->actions->getObject()->pluginTxt($cmd);
    }
}
