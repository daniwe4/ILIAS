<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

namespace CaT\Plugins\CopySettings\CourseCreation;

use CaT\Plugins\CopySettings\Children\Child;
use ILIAS\TMS\CourseCreation\Step;
use ILIAS\TMS\CourseCreation\Request;
use ILIAS\TMS\CourseCreation\RequestBuilder;
use ILIAS\TMS\CourseCreation\ChildAssistant;
use \CaT\Ente\ILIAS\Entity;

/**
 * Step to inform the user about content of training
 */
class DestinationStep extends \CourseCreationStep
{
    use ChildAssistant;

    const F_DESTINATION = "f_destination";
    const F_SELECTED_VALUE = "f_selected_value";

    /**
     * @var	Entity
     */
    protected $entity;

    /**
     * @var	RequestBuilder|null
     */
    protected $request_builder;

    /**
     * @var \Closure
     */
    protected $txt;

    /**
     * @var	\ilObjCopySettings
     */
    protected $object;

    public function __construct(Entity $entity, \Closure $txt, \ilObjCopySettings $object)
    {
        $this->entity = $entity;
        $this->txt = $txt;
        $this->object = $object;
    }

    // from Ente\Component

    /**
     * @inheritdocs
     */
    public function entity()
    {
        return $this->entity;
    }

    // from TMS\Wizard\Step

    /**
     * @inheritdocs
     */
    public function getLabel()
    {
        return $this->txt("destination");
    }

    /**
     * @inheritdocs
     */
    public function getDescription()
    {
        return $this->txt("destination_desc");
    }

    /**
     * @inheritdocs
     */
    public function appendToStepForm(\ilPropertyFormGUI $form)
    {
        global $DIC;
        $log = $DIC->logger()->root();
        $sec = new \ilFormSectionHeaderGUI();
        $sec->setTitle($this->txt("destination"));
        $form->addItem($sec);

        require_once("Services/Form/classes/class.ilCustomInputGUI.php");
        $exp = new ilDestinationSelectorExplorerGUI(null, "");
        $exp->setTypeWhiteList(array("cat"));
        $exp->setSelectMode(self::F_DESTINATION, false);
        $exp->setNodeSelected($this->getParentNode());
        $exp->setHighlightedNode($this->getParentNode());
        if ($exp->handleCommand()) {
            return;
        }
        $output = $exp->getHTML();

        $cui = new \ilCustomInputGUI($this->txt("select_destination"), "table");
        $cui->setHtml($output);

        $form->addItem($cui);

        $hi = new \ilHiddenInputGUI(self::F_SELECTED_VALUE);
        $form->addItem($hi);
    }

    /**
     * @inheritdocs
     */
    public function addDataToForm(\ilPropertyFormGUI $form, $data)
    {
        if (count($data) > 0) {
            $tpl = $this->getDIC()->ui()->mainTemplate();
            $js_file = sprintf(
                '%s/templates/destinationSelection.js',
                $this->object->getPluginDirectory()
            );
            $tpl->addJavaScript($js_file);

            $item = $form->getItemByPostVar(self::F_SELECTED_VALUE);
            $item->setValue($data[self::F_DESTINATION]);
        }
    }

    /**
     * @inheritdocs
     */
    public function appendToOverviewForm(\ilPropertyFormGUI $form, $data)
    {
        require_once("Services/Form/classes/class.ilNonEditableValueGUI.php");
        require_once("Services/Form/classes/class.ilFormSectionHeaderGUI.php");

        $title = \ilObject::_lookupTitle(\ilObject::_lookupObjId($data[self::F_DESTINATION]));
        $item = new \ilNonEditableValueGUI($this->txt("selected_destination"), "", true);
        $item->setValue($title);
        $form->addItem($item);
    }

    /**
     * @inheritdocs
     */
    public function processStep($data)
    {
        $this->request_builder->setNewParentRefId($data[self::F_DESTINATION]);
    }

    /**
     * @inheritdocs
     */
    public function getData(\ilPropertyFormGUI $form)
    {
        $data = array();
        $post = $_POST;
        $data[self::F_DESTINATION] = (int) $post[self::F_DESTINATION];
        return $data;
    }

    // from TMS\CourseCreation\Step

    /**
     * @inheritdocs
     */
    public function getPriority()
    {
        return 50;
    }

    /**
     * @inheritdocs
     */
    public function isApplicable()
    {
        $access = $this->getDIC()->access();
        return $access->checkAccess("choose_course_location", "", $this->object->getRefId()) && $this->hasCategorieToCreateCourseIn();
    }

    /**
     * @inheritdocs
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * @inheritdocs
     */
    public function setRequestBuilder(RequestBuilder $request_builder)
    {
        $this->request_builder = $request_builder;
    }

    /**
     * Get the ref id of entity object
     *
     * @return int
     */
    protected function getEntityRefId()
    {
        return $this->entity()->object()->getRefId();
    }

    /**
     * Get the ILIAS dictionary
     *
     * @return \ArrayAccess | array
     */
    protected function getDIC()
    {
        return $GLOBALS["DIC"];
    }

    /**
     * Get the parent category id of tpl course
     *
     * @return int
     */
    protected function getParentNode()
    {
        return $this->getParentCategory();
    }

    /**
     * Get the parent container
     *
     * @return ilObjCourse | null
     */
    public function getParentCategory()
    {
        global $DIC;
        $tree = $DIC->repositoryTree();
        $parents = array_reverse($tree->getNodePath($this->object->getRefId()));

        foreach ($parents as $parent) {
            if ($parent["type"] == "cat" || $parent["type"] == "root") {
                return $parent["child"];
            }
        }
    }

    /**
     * Is there any category where the user can create courses?
     *
     * @return bool
     */
    protected function hasCategorieToCreateCourseIn()
    {
        global $DIC;
        $access = $DIC->access();

        foreach ($this->getAllReadableCategoryChildrenBreadthFirst(1) as $ref_id) {
            if ($access->checkAccess("create_crs", "", $ref_id)) {
                return true;
            }
        }
        return false;
    }

    protected function getAllReadableCategoryChildrenBreadthFirst($ref_id)
    {
        global $DIC;
        $access = $DIC->access();
        $tree = $DIC->repositoryTree();
        $children = $tree->getChilds($ref_id);

        $interesting_children = [];

        foreach ($children as $child) {
            if ($child["type"] != "cat") {
                continue;
            }
            $ref_id = $child["child"];
            if (!$access->checkAccess("visible", "", $ref_id)
            || !$access->checkAccess("read", "", $ref_id)) {
                continue;
            }
            yield $ref_id;
            $interesting_children[] = $ref_id;
        }

        foreach ($interesting_children as $child_id) {
            foreach ($this->getAllReadableCategoryChildrenBreadthFirst($child_id) as $ref_id) {
                yield $ref_id;
            }
        }
    }

    /**
     * i18n
     *
     * @param	string	$id
     * @return	string	$text
     */
    protected function txt(string $id)
    {
        return call_user_func($this->txt, $id);
    }
}
