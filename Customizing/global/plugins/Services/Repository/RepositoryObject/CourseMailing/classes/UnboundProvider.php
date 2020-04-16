<?php

namespace CaT\Plugins\CourseMailing;

use \CaT\Ente\ILIAS\SeparatedUnboundProvider;
use \CaT\Ente\ILIAS\Entity;
use \ILIAS\TMS\Mailing\MailingOccasion;
use \ILIAS\TMS\ActionBuilder;
use \ILIAS\TMS\CourseCreation as CC;

class UnboundProvider extends SeparatedUnboundProvider
{
    /**
     * @inheritdocs
     */
    public function componentTypes()
    {
        return [
            MailingOccasion::class,
            ActionBuilder::class,
            CC\Step::class
        ];
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
        if ($component_type === MailingOccasion::class) {
            return $this->getMailingOccasions($entity);
        }
        if ($component_type === ActionBuilder::class) {
            return [
                $this->getActionBuilder($entity, $this->owner())
            ];
        }
        if ($component_type === CC\Step::class) {
            return $this->getCourseCreationSteps($entity, $this->owner());
        }

        throw new \InvalidArgumentException("Unexpected component type '$component_type'");
    }

    protected function getActionBuilder(Entity $entity, $owner) : ActionBuilder
    {
        global $DIC;
        $g_user = $DIC->user();
        return new CourseActions\ActionBuilder($entity, $owner, $g_user);
    }

    /**
     * Returns components for automatic mails
     *
     * @return MailingOccasion[]
     */
    protected function getMailingOccasions($entity)
    {
        $owner = $this->owner();

        /** @var ilActions $actions */
        $actions = $owner->getActions();
        $ret = array();

        //get local RoleMapping from CourseMailing
        $mappings = $owner->getRoleMappings();//RoleMapping\RoleMapping[]
        foreach ($mappings as $mapping) {
            $template_id = $mapping->getTemplateId();
            if ($template_id > 0) {
                try {
                    $template = $actions->getMailTemplate($template_id);
                } catch (\OutOfBoundsException $e) {
                    global $DIC;
                    $log = $DIC->logger()->root();
                    $log->write("No template found for id: " . $template_id);
                    continue;
                }

                //mail-template
                $template_ident = $template->getTitle();

                //get all members of this role from course
                $role_id = $mapping->getRoleId();
                $usr_ids = $actions->getCourseMemberIdsWithRole($role_id);

                //get configured attachments
                $attachments = $mapping->getAttachmentIds();

                $ret[] = new AutomaticMails\MailOccasionInvite(
                    $entity,
                    $owner,
                    $template_ident,
                    $usr_ids,
                    $attachments
                );
            }
        }

        $ret[] = new AutomaticMails\MailOccasionFreetext($entity, $owner);
        return $ret;
    }


    /**
     * Get all possible course creation steps.
     *
     * @param Entity 	$entity
     * @param \ilObjCourseMailing	$owner
     *
     * @return CourseCreation\Step[]
     */
    protected function getCourseCreationSteps(Entity $entity, \ilObjCourseMailing  $owner)
    {
        return [
            new CourseCreation\MailingStep($entity, $owner)
        ];
    }
}
