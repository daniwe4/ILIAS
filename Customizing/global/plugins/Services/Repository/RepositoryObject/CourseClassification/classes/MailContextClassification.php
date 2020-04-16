<?php

namespace CaT\Plugins\CourseClassification;

require_once('./Services/TMS/Mailing/classes/class.ilTMSMailContextEnte.php');
require_once('./Services/TMS/Mailing/classes/class.ilTMSMailContextUser.php');

class MailContextClassification extends \ilTMSMailContextEnte
{
    protected static $PLACEHOLDERS = array(
        'COURSE_TYPE' => 'placeholder_desc_coursetype',
        'METHODS' => 'placeholder_desc_methods',
        'OBJECTIVES' => 'placeholder_desc_objectives',
        'TOPICS' => 'placeholder_desc_topics',
        'EDUPROGRAM' => 'placeholder_desc_eduprogram',
        'CATEGORIES' => 'placeholder_desc_categories',
        'MEDIA' => 'placeholder_desc_media',
        'TARGETGROUP' => 'placeholder_desc_targetgroup',
        'TARGETGROUP_DESCRIPTION' => 'placeholder_desc_targetgroup_description',
        'COURSE_CONTENTS' => 'placeholder_desc_content',
        'COURSE_PREPARATION' => 'placeholder_desc_preparation',
        'CONTACT_EMAIL' => 'placeholder_contact_email'
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
        $obj = \ilPluginAdmin::getPluginObjectById("xccl");
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
     * @return $string
     */
    protected function resolveValueFor($id, $contexts)
    {
        $actions = $this->owner->getActions();
        $cc = $actions->getObject()->getCourseClassification();

        switch ($id) {
            case 'COURSE_TYPE':
                $type_id = $cc->getType();
                if ($type_id !== null) {
                    return array_shift($actions->getTypeName($type_id));
                }
                return null;
                break;

            case 'METHODS':
                $ids = $cc->getMethod();
                return implode('<br>', $actions->getMethodNames($ids));
                break;

            case 'OBJECTIVES':
                return $cc->getGoals();
                break;

            case 'TOPICS':
                $ids = $cc->getTopics();
                return implode('<br>', $actions->getTopicsNames($ids));
                break;

            case 'EDUPROGRAM':
                $id = $cc->getEduProgram();
                return array_shift($actions->getEduProgramName($id));
                break;

            case 'CATEGORIES':
                $ids = $cc->getCategories();
                return implode('<br>', $actions->getCategoryNames($ids));
                break;

            case 'MEDIA':
                $ids = $cc->getMedia();
                return implode('<br>', $actions->getMediaNames($ids));
                break;

            case 'TARGETGROUP':
                $ids = $cc->getTargetGroup();
                return implode('<br>', $actions->getTargetGroupNames($ids));
                break;

            case 'TARGETGROUP_DESCRIPTION':
                return $cc->getTargetGroupDescription();
                break;

            case 'COURSE_CONTENTS':
                return $cc->getContent();
                break;

            case 'COURSE_PREPARATION':
                return $cc->getPreparation();
                break;

            case 'CONTACT_EMAIL':
                $mail = "-";
                if ($cc->getContact()->getMail() != "") {
                    $mail = $cc->getContact()->getMail();
                }
                return $mail;
                break;

            default:
                return 'NOT RESOLVED: ' . $id;

        }
    }
}
