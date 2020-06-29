<?php

namespace CaT\Plugins\CourseClassification;

use \CaT\Ente\ILIAS\SeparatedUnboundProvider;
use \CaT\Ente\ILIAS\Entity;
use \ILIAS\TMS\CourseInfo;
use \ILIAS\TMS\CourseInfoImpl;
use \ILIAS\TMS\Mailing;

class UnboundProvider extends SeparatedUnboundProvider
{
    /**
     * @inheritdocs
     */
    public function componentTypes()
    {
        return [CourseInfo::class, Mailing\MailContext::class];
    }

    /**
     * Build the component(s) of the given type for the given object.
     *
     * @param   string    $component_type
     * @param   Entity    $provider
     * @return  Component[]
     */
    public function buildComponentsOf($component_type, Entity $entity)
    {
        // TODO: introduce an object, that can give the real value, not the key.
        // the object needs to bundle $cc and $actions.
        $cc = $this->owner()->getCourseClassification();
        $actions = $this->owner()->getActions();
        $this->txt = $this->owner()->txtClosure();
        if ($component_type === CourseInfo::class) {
            $ret = array();

            $ret = $this->getCourseInfoForType($ret, $entity, $actions, $cc);
            $ret = $this->getCourseInfoForTargetGroups($ret, $entity, $actions, $cc);
            $ret = $this->getCourseInfoForTargetGroupsDescription($ret, $entity, $actions, $cc);
            $ret = $this->getCourseInfoForGoals($ret, $entity, $cc);
            $ret = $this->getCourseInfoForContent($ret, $entity, $cc);
            $ret = $this->getCourseInfoForMethods($ret, $entity, $actions, $cc);
            $ret = $this->getCourseInfoForPreparation($ret, $entity, $cc);
            $ret = $this->getCourseInfoForContact($ret, $entity, $cc);
            $ret = $this->getCourseInfoForAdditionalLinks($ret, $entity, $cc);

            return $ret;
        }

        if ($component_type === Mailing\MailContext::class) {
            return [new MailContextClassification($entity, $this->owner())];
        }
        throw new \InvalidArgumentException("Unexpected component type '$component_type'");
    }

    /**
     * Get course info for type
     *
     * @param CourseInfo[] 	$ret
     * @param Entity 	$entity
     * @param ilActions 	$actions
     * @param Settings\CourseClassification 	$cc
     *
     * @return CourseInfo[]
     */
    protected function getCourseInfoForType(array $ret, Entity $entity, ilActions $actions, Settings\CourseClassification $cc)
    {
        $type_id = $cc->getType();
        if (!is_null($type_id) && $type_id > 0) {
            $type_names = $actions->getTypeName($type_id);
            if (count($type_names) > 0) {
                $type_name = array_shift($type_names);
                if (!is_null($type_name)) {
                    $ret[] = new CourseInfoImpl(
                        $entity,
                        $this->txt("training_type"),
                        $type_name,
                        "",
                        200,
                        [CourseInfo::CONTEXT_SEARCH_SHORT_INFO,
                            CourseInfo::CONTEXT_BOOKING_DEFAULT_INFO,
                            CourseInfo::CONTEXT_USER_BOOKING_SHORT_INFO
                        ]
                    );

                    $ret[] = new CourseInfoImpl(
                        $entity,
                        "type",
                        $type_name,
                        "",
                        500,
                        [
                            CourseInfo::CONTEXT_APPROVALS_OVERVIEW
                        ]
                    );
                }
            }
        }

        return $ret;
    }

    /**
     * Get course info for target groups
     *
     * @param CourseInfo[] 	$ret
     * @param Entity 	$entity
     * @param ilActions 	$actions
     * @param Settings\CourseClassification 	$cc
     *
     * @return CourseInfo[]
     */
    protected function getCourseInfoForTargetGroups(array $ret, Entity $entity, ilActions $actions, Settings\CourseClassification $cc)
    {
        $target_groups = $actions->getTargetGroupNames($cc->getTargetGroup());
        if (count($target_groups) > 0 && $target_groups[0] != "-") {
            $ret[] = $this->buildDetailInfo(
                $entity,
                $this->txt("target_groups"),
                $target_groups,
                400,
                [CourseInfo::CONTEXT_SEARCH_DETAIL_INFO]
            );

            $ret[] = $this->buildDetailInfo(
                $entity,
                $this->txt("target_groups"),
                $target_groups,
                700,
                [CourseInfo::CONTEXT_USER_BOOKING_DETAIL_INFO,
                  CourseInfo::CONTEXT_ASSIGNED_TRAINING_DETAIL_INFO,
                  CourseInfo::CONTEXT_ADMIN_OVERVIEW_DETAIL_INFO
                ]
            );

            $ret[] = $this->buildDetailInfo(
                $entity,
                $this->txt("target_groups"),
                $target_groups,
                300,
                [CourseInfo::CONTEXT_BOOKING_DEFAULT_INFO]
            );
        }

        return $ret;
    }

    /**
     * Get course info for target group description
     *
     * @param CourseInfo[] 	$ret
     * @param Entity 	$entity
     * @param Settings\CourseClassification 	$cc
     *
     * @return CourseInfo[]
     */
    protected function getCourseInfoForTargetGroupsDescription(array $ret, Entity $entity, ilActions $actions, Settings\CourseClassification $cc)
    {
        $target_group_description = $cc->getTargetGroupDescription();
        if (trim($target_group_description) != "") {
            $ret[] = $this->buildDetailInfo(
                $entity,
                $this->txt("target_group_description"),
                nl2br($target_group_description),
                500,
                [CourseInfo::CONTEXT_SEARCH_DETAIL_INFO]
            );

            $ret[] = $this->buildDetailInfo(
                $entity,
                $this->txt("target_group_description"),
                nl2br($target_group_description),
                800,
                [CourseInfo::CONTEXT_USER_BOOKING_DETAIL_INFO,
                  CourseInfo::CONTEXT_ASSIGNED_TRAINING_DETAIL_INFO,
                  CourseInfo::CONTEXT_ADMIN_OVERVIEW_DETAIL_INFO
                ]
            );

            $target_groups = $actions->getTargetGroupNames($cc->getTargetGroup());
            $label = "";
            if (count($target_groups) == 0 ||
                array_shift($target_groups) == "-"
            ) {
                $label = $this->txt("target_groups");
            }
            $ret[] = $this->buildDetailInfo(
                $entity,
                $label,
                nl2br($target_group_description),
                400,
                [CourseInfo::CONTEXT_BOOKING_DEFAULT_INFO]
            );
        }

        return $ret;
    }

    /**
     * Get course info for goals
     *
     * @param CourseInfo[] 	$ret
     * @param Entity 	$entity
     * @param Settings\CourseClassification 	$cc
     *
     * @return CourseInfo[]
     */
    protected function getCourseInfoForGoals(array $ret, Entity $entity, Settings\CourseClassification $cc)
    {
        $goals = $cc->getGoals();
        if (trim($goals) != "") {
            $ret[] = $this->buildDetailInfo(
                $entity,
                $this->txt("goals"),
                nl2br($goals),
                300,
                [CourseInfo::CONTEXT_SEARCH_DETAIL_INFO]
            );

            $ret[] = $this->buildDetailInfo(
                $entity,
                $this->txt("goals"),
                nl2br($goals),
                600,
                [CourseInfo::CONTEXT_USER_BOOKING_DETAIL_INFO,
                  CourseInfo::CONTEXT_ASSIGNED_TRAINING_DETAIL_INFO,
                  CourseInfo::CONTEXT_ADMIN_OVERVIEW_DETAIL_INFO
                ]
            );

            $ret[] = $this->buildDetailInfo(
                $entity,
                $this->txt("goals"),
                nl2br($goals),
                500,
                [CourseInfo::CONTEXT_BOOKING_DEFAULT_INFO]
            );

            $ret[] = $this->buildDetailInfo(
                $entity,
                "goals",
                nl2br($goals),
                800,
                [CourseInfo::CONTEXT_APPROVALS_OVERVIEW]
            );
        }

        return $ret;
    }

    /**
     * Get course info for content
     *
     * @param CourseInfo[] 	$ret
     * @param Entity 	$entity
     * @param Settings\CourseClassification 	$cc
     *
     * @return CourseInfo[]
     */
    protected function getCourseInfoForContent(array $ret, Entity $entity, Settings\CourseClassification $cc)
    {
        $content = $cc->getContent();
        if (trim($content) != "") {
            $ret[] = $this->buildDetailInfo(
                $entity,
                $this->txt("content"),
                nl2br($content),
                200,
                [CourseInfo::CONTEXT_SEARCH_DETAIL_INFO]
            );

            $ret[] = $this->buildDetailInfo(
                $entity,
                $this->txt("content"),
                nl2br($content),
                500,
                [CourseInfo::CONTEXT_USER_BOOKING_DETAIL_INFO,
                  CourseInfo::CONTEXT_ASSIGNED_TRAINING_DETAIL_INFO,
                  CourseInfo::CONTEXT_ADMIN_OVERVIEW_DETAIL_INFO
                ]
            );

            $ret[] = $this->buildDetailInfo(
                $entity,
                $this->txt("content"),
                nl2br($content),
                600,
                [
                    CourseInfo::CONTEXT_BOOKING_DEFAULT_INFO
                ]
            );

            $ret[] = $this->buildDetailInfo(
                $entity,
                "content",
                nl2br($content),
                600,
                [
                    CourseInfo::CONTEXT_APPROVALS_OVERVIEW
                ]
            );
        }

        return $ret;
    }

    /**
     * Get course info for mehods
     *
     * @param CourseInfo[] 	$ret
     * @param Entity 	$entity
     * @param ilActions 	$actions
     * @param Settings\CourseClassification 	$cc
     *
     * @return CourseInfo[]
     */
    protected function getCourseInfoForMethods(array $ret, Entity $entity, ilActions $actions, Settings\CourseClassification $cc)
    {
        $method = $actions->getMethodNames($cc->getMethod());
        if (count($method) > 0 && $method[0] != "-") {
            $ret[] = $this->buildDetailInfo(
                $entity,
                $this->txt("method"),
                $method,
                800,
                [CourseInfo::CONTEXT_SEARCH_DETAIL_INFO,
                  CourseInfo::CONTEXT_USER_BOOKING_DETAIL_INFO,
                  CourseInfo::CONTEXT_BOOKING_DEFAULT_INFO,
                  CourseInfo::CONTEXT_ASSIGNED_TRAINING_DETAIL_INFO,
                  CourseInfo::CONTEXT_ADMIN_OVERVIEW_DETAIL_INFO
                ]
            );
        }

        return $ret;
    }

    /**
     * Get course info for preparation
     *
     * @param CourseInfo[] 	$ret
     * @param Entity 	$entity
     * @param Settings\CourseClassification 	$cc
     *
     * @return CourseInfo[]
     */
    protected function getCourseInfoForPreparation(array $ret, Entity $entity, Settings\CourseClassification $cc)
    {
        $preparation = $cc->getPreparation();
        if (trim($preparation) != "") {
            $ret[] = $this->buildDetailInfo(
                $entity,
                $this->txt("preparation"),
                nl2br($preparation),
                900,
                [CourseInfo::CONTEXT_SEARCH_DETAIL_INFO,
                  CourseInfo::CONTEXT_USER_BOOKING_DETAIL_INFO,
                  CourseInfo::CONTEXT_ADMIN_OVERVIEW_DETAIL_INFO
                ]
            );
        }

        return $ret;
    }

    /**
     * Get course info for contact
     *
     * @param CourseInfo[] 	$ret
     * @param Entity 	$entity
     * @param Settings\CourseClassification 	$cc
     *
     * @return CourseInfo[]
     */
    protected function getCourseInfoForContact(array $ret, Entity $entity, Settings\CourseClassification $cc)
    {
        $contact = $cc->getContact();
        if ($contact->getName() != "") {
            $name = $contact->getName();
            $phone = $contact->getPhone();
            $mail = $contact->getMail();
            $text = "";

            if ($phone != "" && $mail != "") {
                $text = " (" . $phone . ", " . $mail . ")";
            } elseif ($phone != "" && $mail == "") {
                $text = " (" . $phone . ")";
            } elseif ($phone == "" && $mail != "") {
                $text = " (" . $mail . ")";
            }

            $ret[] = new CourseInfoImpl(
                $entity,
                $this->txt("contact"),
                $name . $text,
                "",
                1900,
                [CourseInfo::CONTEXT_BOOKING_DEFAULT_INFO]
            );
        }

        return $ret;
    }

    protected function getCourseInfoForAdditionalLinks(array $ret, Entity $entity, Settings\CourseClassification $cc) : array
    {
        $links = $cc->getAdditionalLinks();
        if (count($links) > 0) {
            $links_txt = [];
            $tpl = '<a href="%s" target="_blank">%s</a>';

            foreach ($links as $link) {
                $links_txt[] = sprintf(
                    $tpl,
                    strip_tags(stripslashes($link->getUrl())),
                    $link->getLabel()
                );
            }

            $ret[] = new CourseInfoImpl(
                $entity,
                $this->txt("additional_links"),
                $links_txt,
                "",
                2000,
                [
                    CourseInfo::CONTEXT_USER_BOOKING_DETAIL_INFO,
                    CourseInfo::CONTEXT_SEARCH_DETAIL_INFO
                ]
            );
        }

        return $ret;
    }


    /**
     * Createa a courseInfoImpl object for detailed infos
     *
     * @param Entity 	$entity
     * @param string 	$lable
     * @param string 	$value
     * @param int 	$step
     * @param string 	$contexts
     *
     * @return CourseInfoImpl
     */
    protected function buildDetailInfo($entity, $label, $value, $step, array $contexts)
    {
        return new CourseInfoImpl(
            $entity,
            $label,
            $value,
            "",
            $step,
            $contexts
        );
    }

    /**
     * Parse lang code to lang value
     *
     * @param string 	$code
     *
     * @return string
     */
    protected function txt($code)
    {
        $txt = $this->txt;
        return $txt($code);
    }
}
