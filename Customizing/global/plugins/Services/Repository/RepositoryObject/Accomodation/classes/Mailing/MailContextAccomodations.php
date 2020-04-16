<?php
namespace CaT\Plugins\Accomodation\Mailing;

use CaT\Plugins\Accomodation\ilActions;

require_once('./Services/TMS/Mailing/classes/class.ilTMSMailContextEnte.php');
require_once('./Services/TMS/Mailing/classes/class.ilTMSMailContextUser.php');

/**
 * Provide placeholders in the context of accomodations
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class MailContextAccomodations extends \ilTMSMailContextEnte
{
    protected static $PLACEHOLDERS = array(
        'ACCOMODATION' => 'placeholder_desc_accomodation_address',
        'ALL_ACCOMODATIONS' => 'placeholder_desc_all_accomodation_addresses',
        'OVERNIGHTS' => 'placeholder_desc_overnights',
    );

    /**
     * @inheritdoc
     */
    public function placeholderIds()
    {
        return array_keys(self::$PLACEHOLDERS);
    }

    /**
     * @inheritdoc
     */
    public function placeholderDescriptionForId($placeholder_id)
    {
        $desc = self::$PLACEHOLDERS[$placeholder_id];
        $obj = \ilPluginAdmin::getPluginObjectById("xoac");
        return $obj->txt($desc);
    }

    /**
     * @inheritdoc
     */
    public function valueFor($placeholder_id, $contexts = array())
    {
        if (!in_array($placeholder_id, $this->placeholderIds())) {
            return null;
        }
        return $this->resolveValueFor($placeholder_id, $contexts);
    }

    /**
     * @param string $id
     * @param MailContexts[] $contexts
     * @return $string | null
     */
    protected function resolveValueFor($id, $contexts)
    {
        $actions = [];
        $all_oac_contexts = $this->getAllAccomodationContexts($contexts);
        foreach ($all_oac_contexts as $oac_context) {
            $actions[] = $oac_context->owner()->getActions();
        }

        switch ($id) {
            case 'ALL_ACCOMODATIONS':
                $accomodations = [];
                foreach ($actions as $action) {
                    $loc = $action->getLocation();
                    if (!is_null($loc)) {
                        $loc = $loc->getHTML(PHP_EOL);
                        if (!in_array($loc, $accomodations) && !is_null($loc)) {
                            $accomodations[] = $loc;
                        }
                    }
                }
                if (count($accomodations) > 0) {
                    return implode(PHP_EOL . PHP_EOL, $accomodations);
                }
                return null;

            case 'ACCOMODATION':
                $user_context = $this->getUserContextFromContexts($contexts);
                if (is_null($user_context)) {
                    return null;
                }

                $accomodations = [];
                foreach ($actions as $action) {
                    $loc = $action->getLocation();

                    if (!is_null($loc)) {
                        $loc = $loc->getHTML(PHP_EOL);

                        if (!in_array($loc, $accomodations)) {
                            $accomodations[] = $loc;
                        }
                    }
                }

                return implode(PHP_EOL . PHP_EOL, $accomodations);

            case 'OVERNIGHTS':
                $user_context = $this->getUserContextFromContexts($contexts);
                if (is_null($user_context)) {
                    return null;
                }
                $usr_id = (int) $user_context->getUsrId();

                $overnights = [];
                foreach ($actions as $action) {
                    $loc = $action->getLocation();
                    if (!is_null($loc)) {
                        $loc_html = $loc->getName();
                    } else {
                        $loc_html = '-';
                    }

                    $reservations = $this->getReservationsForOutput(
                        $usr_id,
                        $action
                    );
                    if (count($reservations) > 0) {
                        $overnights[$loc_html] = $reservations;
                    }
                }

                $obj = \ilPluginAdmin::getPluginObjectById("xoac");
                if (count($overnights) === 0) {
                    return $obj->txt("no_overnight_selected");
                }

                $ret = [];
                foreach ($overnights as $venue => $entries) {
                    $ret[] = $venue;
                    foreach ($entries as $entry) {
                        $ret[] = $entry;
                    }
                }
                return implode(PHP_EOL, $ret);

            default:
                return 'NOT RESOLVED: ' . $id;

        }
    }

    /**
     * Return the user-context from $contexts or null
     *
     * @param MailContext[] $contexts
     * @return MailContext | null
     */
    private function getUserContextFromContexts($contexts)
    {
        foreach ($contexts as $context) {
            if ($context instanceof \ilTMSMailContextUser) {
                return $context;
            }
        }
        return null;
    }

    /**
     * filter for Accomodation-Contexts
     *
     * @param MailContext[] $contexts
     * @return MailContext[]
     */
    private function getAllAccomodationContexts($contexts)
    {
        return array_filter(
            $contexts,
            function ($context) {
                return $context instanceof $this;
            }
        );
    }

    protected function getReservationsForOutput(int $usr_id, ilActions $actions)
    {
        $user_reservations = $actions->getUserReservationsAtObj($usr_id)->getReservations();
        $this_obj_id = $actions->getObjId();
        $object = $actions->getObject();

        $txt = $object->getTxtClosure();
        $res = array();

        $prior_night = $actions->getPriorNightDate();
        $post_night = $actions->getPostNightDate();

        foreach ($user_reservations as $user_reservation) {
            $r_oac_id = $user_reservation->getAccomodationObjId();

            $dat = $user_reservation->getDate()->get(IL_CAL_DATE);
            $label = $actions->formatDate($dat, true);
            $label_next = $actions->getNextDayLabel($dat);

            if ($dat === $prior_night) {
                $label = $txt('priorday') . ' - ' . $label_next;
            }
            if ($dat === $post_night) {
                $label = $label . ' - ' . $txt('postday');
            }
            if ($dat != $prior_night && $dat != $post_night) {
                $label = $label . ' - ' . $label_next;
            }

            if ($r_oac_id !== $this_obj_id) {
                $label .= ' (';
                if ($user_reservation->getSelfpay()) {
                    $label .= $txt('table_user_edit_is_selfpay') . ' / ';
                }
                $label .= \ilObject::_lookupTitle($r_oac_id) . ')';
            } else {
                if ($user_reservation->getSelfpay()) {
                    $label .= ' (' . $txt('table_user_edit_is_selfpay') . ')';
                }
            }

            $ut = $user_reservation->getDate()->get(IL_CAL_UNIX);
            $res[$ut] = $label;
        }
        ksort($res);
        return array_values($res);
    }
}
