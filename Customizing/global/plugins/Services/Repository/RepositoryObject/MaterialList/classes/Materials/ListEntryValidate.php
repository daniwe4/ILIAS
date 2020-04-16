<?php

declare(strict_types=1);

namespace CaT\Plugins\MaterialList\Materials;

use \CaT\Plugins\MaterialList;

/**
 * Validates list entries on user input
 */
class ListEntryValidate
{
    public function __construct(MaterialList\ilPluginActions $plugin_actions)
    {
        $this->plugin_actions = $plugin_actions;
    }

    /**
     * Check to save list entries
     *
     * @param MaterialList\Lists\CheckObject 	$check_object
     * @param array 								&$ret
     * @param int 									$position
     *
     * @return null
     */
    public function validateEntry(MaterialList\Lists\CheckObject $check_object, array &$ret, $position)
    {
        assert('is_int($position)');

        if ($check_object->getId() == -1
                && $this->noInput(
                    $check_object->getNumberPerParticipant(),
                    $check_object->getNumberPerCourse(),
                    $check_object->getArticleNumber(),
                    $check_object->getTitle()
                )
        ) {
            $ret["empty"][$position] = $check_object;
            return;
        }

        //Check article number if behavior is auto complete
        if ($this->plugin_actions->getBehavior() == MaterialList\ilPluginActions::MATERIAL_MODE_AUTO_COMPLETE) {
            if (!$this->plugin_actions->articleNumberKnown($check_object->getArticleNumber())) {
                $ret["faults"]["objects"][$position] = $check_object;
                $ret["faults"]["values"][$position][MaterialList\ilObjectActions::F_LIST_ENTRY_TITLE][] = "list_article_number_not_known";
                $fault = true;
            }

            if ($this->noArticleNumber($check_object->getArticleNumber())) {
                $ret["faults"]["objects"][$position] = $check_object;
                $ret["faults"]["values"][$position][MaterialList\ilObjectActions::F_LIST_ENTRY_TITLE][] = "list_no_article_number";
                $fault = true;
            }
        }

        if ($this->articleNumberTooLong($check_object->getArticleNumber())) {
            $ret["faults"]["objects"][$position] = $check_object;
            $ret["faults"]["values"][$position][MaterialList\ilObjectActions::F_LIST_ENTRY_TITLE][] = "list_article_number_to_long";
            $fault = true;
        }

        if ($this->noTitle($check_object->getTitle())) {
            $ret["faults"]["objects"][$position] = $check_object;
            $ret["faults"]["values"][$position][MaterialList\ilObjectActions::F_LIST_ENTRY_TITLE][] = "list_no_title";
            $fault = true;
        }

        if ($this->titleTooLong($check_object->getTitle())) {
            $ret["faults"]["objects"][$position] = $check_object;
            $ret["faults"]["values"][$position][MaterialList\ilObjectActions::F_LIST_ENTRY_TITLE][] = "list_title_to_long";
            $fault = true;
        }

        if ($this->numberNegative($check_object->getNumberPerParticipant())) {
            $ret["faults"]["objects"][$position] = $check_object;
            $ret["faults"]["values"][$position][MaterialList\ilObjectActions::F_LIST_ENTRY_NUMPER_PER_PARTICIPANT][] = "list_number_per_participant_negative";
            $fault = true;
        }

        if ($this->numberNegative($check_object->getNumberPerCourse())) {
            $ret["faults"]["objects"][$position] = $check_object;
            $ret["faults"]["values"][$position][MaterialList\ilObjectActions::F_LIST_ENTRY_NUMBER_PER_COURSE][] = "list_number_per_course_negative";
            $fault = true;
        }

        if ($check_object->getNumberPerParticipant() !== "" && $this->numberInputIsString($check_object->getNumberPerParticipant())) {
            $ret["faults"]["objects"][$position] = $check_object;
            $ret["faults"]["values"][$position][MaterialList\ilObjectActions::F_LIST_ENTRY_NUMPER_PER_PARTICIPANT][] = "list_number_per_string";
            $fault = true;
        }

        if ($check_object->getNumberPerCourse() !== null && $this->numberInputIsString($check_object->getNumberPerCourse())) {
            $ret["faults"]["objects"][$position] = $check_object;
            $ret["faults"]["values"][$position][MaterialList\ilObjectActions::F_LIST_ENTRY_NUMBER_PER_COURSE][] = "list_number_per_string";
            $fault = true;
        }

        if ($this->numberPerParticipant($check_object->getNumberPerParticipant())) {
            $ret["tooMuch"] = true;
        }

        if (!$fault) {
            $ret["correct"][$position] = $check_object;
        }
    }

    protected function noInput(
        string $number_per_participant,
        string $number_per_course,
        string $article_number,
        string $title
    ) : bool {
        return $number_per_participant == "" &&
            $number_per_course == "" &&
            $article_number == "" &&
            $title == ""
        ;
    }

    protected function noArticleNumber(string $article_number) : bool
    {
        if ($article_number == "") {
            return true;
        }

        if ($article_number === null) {
            return true;
        }

        return false;
    }

    protected function articleNumberTooLong(string $article_number) : bool
    {
        return strlen($article_number) > MaterialList\ilObjectActions::MAX_ARTICLE_NUMBER_LENGTH;
    }

    protected function noTitle(string $title) : bool
    {
        if ($title == "") {
            return true;
        }

        if ($title === null) {
            return true;
        }

        return false;
    }

    protected function titleTooLong(string $title) : bool
    {
        return strlen($title) > MaterialList\ilObjectActions::MAX_TITLE_LENGTH;
    }

    protected function numberNegative(string $number) : bool
    {
        return (int) $number < 0;
    }

    protected function numberInputIsString(string $string_number) : bool
    {
        return (bool) preg_match('/([A-Z]|[a-z])/', $string_number);
    }

    protected function numberPerParticipant(string $number_per_participant) : bool
    {
        return (int) $number_per_participant > MaterialList\ilObjectActions::MAX_NUMBER_PER_PARTICIPANT;
    }
}
