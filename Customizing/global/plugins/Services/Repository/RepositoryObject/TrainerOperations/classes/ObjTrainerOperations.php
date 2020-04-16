<?php

namespace CaT\Plugins\TrainerOperations;

/**
 * Interface for the pluginobject to make it more testable.
 *
 * @author
 * @copyright Extended GPL, see LICENSE
 */
interface ObjTrainerOperations
{
    const PLUGIN_ID = 'xtep';
    const PLUGIN_NAME = 'TrainerOperations';

    const ORGU_OP_SEE_OTHER_CALENDARS = 'orgu_see_other_calendars';
    const OP_EDIT_OWN_CALENDARS = 'edit_own_calendars';
    const OP_EDIT_GENERAL_CALENDARS = 'edit_general_calendars';
    const OP_SEE_UNASSIGNED = 'see_trainings_without_tutors';
    const OP_SEE_GENERAL = 'see_general_calendars';
}
