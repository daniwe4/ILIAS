<?php

use PHPUnit\Framework\TestCase;

/**
 * Testcase for determining the correct parent container object
 *
 * @author Stefan Hecken	<stefan.hecken@concspts-and-training.de>
 *
 * @group needsInstalledILIAS
 */
class DetermineParentContainerTest extends TestCase
{
    public function setUp() : void
    {
        require_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
        ilUnitUtil::performInitialisation();

        require_once("./Modules/Course/classes/class.ilObjCourse.php");
        $this->crs = new ilObjCourse();
        $this->crs->create(true);
        $this->crs->setTitle("TestCourse");
        $this->crs->update();
        $this->crs->createReference();

        require_once("./Modules/Category/classes/class.ilObjCategory.php");
        $this->cat = new ilObjCategory();
        $this->cat->create(true);
        $this->cat->setTitle("TestCategory");
        $this->cat->update();
        $this->cat->createReference();

        require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CopySettings/classes/class.ilObjCopySettings.php");
        $this->cs = new ilObjCopySettings();
        $this->cs->create(true);
        $this->cs->setTitle("CopySettings");
        $this->cs->update();
        $this->cs->createReference();

        $this->cat->putInTree(ROOT_FOLDER_ID);
    }

    public function tearDown() : void
    {
        $this->cat->delete();
    }

    public function test_parentWithChildContainer()
    {
        $this->crs->putInTree($this->cat->getRefId());
        $this->cs->putInTree($this->cat->getRefId());

        $actions = $this->cs->getActions();
        $parent = $actions->getParentContainer();

        $this->assertEquals($this->cat->getRefId(), $parent->getRefId());
        $this->assertEquals($this->cat->getId(), $parent->getId());
        $this->assertEquals($this->cat->getType(), $parent->getType());
    }

    public function test_parentWithContainerAsChild()
    {
        $this->crs->putInTree($this->cat->getRefId());
        $this->cs->putInTree($this->crs->getRefId());

        $actions = $this->cs->getActions();
        $parent = $actions->getParentContainer();

        $this->assertEquals($this->crs->getRefId(), $parent->getRefId());
        $this->assertEquals($this->crs->getId(), $parent->getId());
        $this->assertEquals($this->crs->getType(), $parent->getType());
    }
}
