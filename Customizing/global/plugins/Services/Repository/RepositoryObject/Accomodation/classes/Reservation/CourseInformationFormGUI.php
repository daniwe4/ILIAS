<?php

namespace CaT\Plugins\Accomodation\Reservation;

/**
 * Show information about the course and it's timetable.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
trait CourseInformationFormGUI
{

    /**
     * Enrich form with course information.
     *
     * @param \ilPropertyFormGUI 	$form
     * @return \ilPropertyFormGUI
     */
    protected function initInformationForm($form)
    {
        $course_settings = $this->actions->getCourseInformation();
        $timetable = $this->actions->getSessionsTimeTable();
        $location = $this->actions->getLocation();
        //$location = $this->actions->getLocationRepresentation();

        $section = new \ilFormSectionHeaderGUI();
        $section->setTitle($this->txt('reservation_section_course_info'));
        $form->addItem($section);

        $ne = new \ilNonEditableValueGUI($this->txt('course_title'), Constants::F_COURSE_TITLE);
        $ne->setValue($course_settings['title']);
        $ne->setInfo($course_settings['description']);
        $form->addItem($ne);

        $ne = new \ilNonEditableValueGUI($this->txt('course_timetable'), Constants::F_COURSE_TIMETABLE, true);
        $ne->setValue(implode('<br>', $timetable));
        $form->addItem($ne);

        $ne = new \ilNonEditableValueGUI($this->txt('accomodation_location'), Constants::F_ACCOMODATION_LOCATION, true);
        if ($location) {
            $ne->setValue((string) $location->getHTML());
        } else {
            \ilUtil::sendInfo($this->txt("no_location_configured"));
        }
        $form->addItem($ne);

        return $form;
    }
}
