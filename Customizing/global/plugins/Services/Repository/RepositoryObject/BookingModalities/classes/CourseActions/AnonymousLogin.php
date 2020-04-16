<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

namespace CaT\Plugins\BookingModalities\CourseActions;

use ILIAS\TMS;
use ILIAS\UI;
use ILIAS\UI\Implementation\Component\Modal\Modal;

/**
 * This is an information about a course action, noteworthy for a user in some context.
 */
class AnonymousLogin extends TMS\CourseActionImpl
{
    /**
     * @inheritdoc
     */
    public function isAllowedFor($usr_id)
    {
        $course = $this->entity->object();

        $crs_start = $course->getCourseStart();
        $crs_member = $course->getMembersObject()->getCountMembers();

        $max_member = $this->owner->getMember()->getMax();
        $booking_deadline = $this->owner->getBooking()->getDeadline();
        $booking_beginning = $this->owner->getBooking()->getBeginning();
        $modus = $this->owner->getBooking()->getModus();

        require_once(__DIR__ . "/../Settings/class.ilBookingModalitiesGUI.php");
        $is_self_booking = $modus === \ilBookingModalitiesGUI::SELF_BOOKING;

        $approval_required = count($this->owner->getApproversPositions()) > 0;

        return $usr_id == ANONYMOUS_USER_ID &&
            $this->bookable($crs_member, $max_member) &&
            $this->isInBookingPeriod($crs_start, $booking_beginning, $booking_deadline) &&
            $is_self_booking &&
            !$approval_required
        ;
    }

    /**
     * @inheritdoc
     */
    public function getLink(\ilCtrl $ctrl, $usr_id)
    {
        $course = $this->entity->object();
        $link = \ilLink::_getStaticLink($this->owner->getRefId(), 'xbkm', true, "_crs" . $course->getRefId());
        return $link;
    }

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        $txt = $this->owner->txtClosure();
        return $txt("login_and_book");
    }

    /**
     * Get status of booking for this course
     *
     * @param int 	$crs_member
     * @param int 	$max_member
     *
     * @return string
     */
    protected function bookable($crs_member, $max_member)
    {
        if ($max_member === null || ($crs_member < $max_member)) {
            return true;
        }
        return false;
    }

    /**
     * Is today in booking period of course
     *
     * @param ilDateTime 	$crs_start
     * @param int 	$booking_start
     * @param int 	$booking_end
     *
     * @return bool
     */
    public function isInBookingPeriod(\ilDateTime $crs_start = null, $booking_start, $booking_end)
    {
        if ($crs_start == null) {
            return true;
        }

        $today_string = date("Y-m-d");

        $booking_start_date = clone $crs_start;
        $booking_start_date->increment(\ilDateTime::DAY, -1 * $booking_start);
        $start_string = $booking_start_date->get(IL_CAL_DATE);

        $booking_end_date = clone $crs_start;
        $booking_end_date->increment(\ilDateTime::DAY, -1 * $booking_end);
        $end_string = $booking_end_date->get(IL_CAL_DATE);

        if ($today_string >= $start_string && $today_string <= $end_string) {
            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function hasModal()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getModal(\ilCtrl $ctrl, UI\Factory $factory, int $usr_id) : Modal
    {
        $txt = $this->owner->txtClosure();

        $form = new \ilPropertyFormGUI();
        $form->setFormAction($this->getLink($ctrl, $usr_id));
        $form->setId("form_" . $this->owner->getId() . $usr_id);

        $form_id = 'form_' . $form->getId();
        $login = $factory->button()->primary($txt('btn_login_and_book'), "#")->withOnLoadCode(function ($id) use ($form_id) {
            return "$('#{$id}').click(function() { $('#{$form_id}').submit(); return false; });";
        });

        $register_link = $ctrl->getLinkTargetByClass(
            [
                "ilStartUpGUI",
                "ilaccountregistrationgui"
            ],
            "",
            "",
            false,
            false
        );
        if (\ilPluginAdmin::isPluginActive("registration")) {
            //$ctrl->initBaseClass("ilStartUpGUI");
            $register_link = $ctrl->getLinkTargetByClass(
                [
                    "ilStartUpGUI",
                    "ilSelfRegistrationGUI"
                ],
                "doStandardAuthentication",
                "",
                false,
                false
            );
        }

        $register = $factory->button()->standard($txt('btn_register'), "#")->withOnLoadCode(function ($id) use ($form_id, $register_link) {
            return "$('#{$id}').click(function() {
				$('#{$form_id}').attr('action', '{$register_link}');
				$('#{$form_id}').submit();
				return false;
			});";
        });

        $content = sprintf($txt("anonymous_decision_form_text"), $txt('btn_login_and_book'), $txt('btn_register')) . $form->getHTML();
        $modal = $factory->modal()->roundtrip($txt('anonymous_decision_title'), $factory->legacy($content))
            ->withActionButtons([$login, $register]);

        return $modal;
    }
}
