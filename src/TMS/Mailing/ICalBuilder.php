<?php
namespace ILIAS\TMS\Mailing;

/**
 * Interface ICalBuilder
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
interface ICalBuilder
{
    /**
     * Get an iCal string for the course info.
     *
     * @param	string	$ref some string referenceing the appointment
     * @param 	ILIAS\TMS\CourseInfo[] $info
     * @return 	string
     */
    public function getIcalString(string $ref, array $info) : string;

    /**
     * Creates a iCal file in a temporary directory and return its path.
     *
     * @param 	ILIAS\TMS\CourseInfo[] $info
     * @param 	string 	$file_name
     * @return 	string
     */
    public function saveICal(string $ref, array $info, string $file_name) : string;
}
