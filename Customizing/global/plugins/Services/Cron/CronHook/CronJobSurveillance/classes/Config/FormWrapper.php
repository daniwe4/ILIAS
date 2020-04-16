<?php
namespace CaT\Plugins\CronJobSurveillance\Config;

/**
 * Interface
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
interface FormWrapper
{
    /**
     * Get the generated HTML.
     *
     * @return 	string
     */
    public function getHtml();

    /**
     * Save the user input.
     *
     * @param array 	$input
     */
    public function setInputByArray(array $input);

    /**
     * Checks the post array for valid values.
     *
     * @return void
     */
    public function checkInput();
}
