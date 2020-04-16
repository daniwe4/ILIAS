<?php
namespace CaT\Plugins\CourseMember;

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseMember/classes/class.ilObjCourseMember.php");

use \CaT\Ente\ILIAS\SeparatedUnboundProvider;
use \CaT\Ente\ILIAS\Entity;
use \ILIAS\TMS\ActionBuilder;
use \CaT\Plugins\CourseMember\Mailing\MailContextCourseMember;
use \ILIAS\TMS\Mailing as TMSMailing;
use \ILIAS\TMS\File;
use \ILIAS\TMS\FileImpl;

/**
* Provide mail-context in global scope.
*
* @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
* @author Nils Haagen <nils.haagen@concepts-and-training.de>
*/
class UnboundProvider extends SeparatedUnboundProvider
{
    /**
     * @inheritdocs
     */
    public function componentTypes()
    {
        return [
            TMSMailing\MailContext::class,
            ActionBuilder::class,
            File::class
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
        $owner = $this->owner();
        if ($component_type === ActionBuilder::class) {
            return [
                $this->getActionBuilder($entity, $owner)
            ];
        }

        if ($component_type === TMSMailing\MailContext::class) {
            return [new MailContextCourseMember($entity, $owner)];
        }
        if ($component_type === File::class) {
            return $this->getCourseMemberFiles($entity, $owner);
        }
        return array();
    }

    protected function getActionBuilder(Entity $entity, $owner) : ActionBuilder
    {
        global $DIC;
        $g_user = $DIC->user();
        return new CourseActions\ActionBuilder($entity, $owner, $g_user);
    }

    /**
     * Get all possible files provided by this object
     *
     * @return File[]
     */
    protected function getCourseMemberFiles(Entity $entity, \ilObject $owner)
    {
        return array(
            new FileImpl(
                'signaturelist_export',
                'application/pdf',
                $entity,
                $owner,
                function ($owner) {
                    return $owner->getActions()->exportSignatureList();
                }
            )
        );
    }
}
