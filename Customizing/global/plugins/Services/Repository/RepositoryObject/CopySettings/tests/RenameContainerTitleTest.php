<?php

use PHPUnit\Framework\TestCase;

/**
 * Testcase for rename the title of container objects
 *
 * @author Stefan Hecken	<stefan.hecken@concspts-and-training.de>
 *
 * @group needsInstalledILIAS
 */
class RenameContainerTitleTest extends TestCase
{
    const TEMPLATE_PREFIX = "Vorlage";

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
        $this->crs->putInTree(ROOT_FOLDER_ID);

        require_once("./Modules/Category/classes/class.ilObjCategory.php");
        $this->cat = new ilObjCategory();
        $this->cat->create(true);
        $this->cat->setTitle("TestCategory");
        $this->cat->update();
        $this->cat->createReference();
        $this->cat->putInTree(ROOT_FOLDER_ID);

        require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CopySettings/classes/class.ilObjCopySettings.php");
        $this->cs = new ilObjCopySettings();
        $this->cs->create(true);
        $this->cs->setTitle("CopySettings");
        $this->cs->update();
        $this->cs->createReference();

        require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CopySettings/classes/class.ilObjCopySettings.php");
        $this->cs2 = new ilObjCopySettings();
        $this->cs2->create(true);
        $this->cs2->setTitle("CopySettings2");
        $this->cs2->update();
        $this->cs2->createReference();
    }

    public function tearDown() : void
    {
        $this->cs->delete();
        $this->cs2->delete();
        $this->crs->delete();
        $this->cat->delete();
    }

    public function test_markTemplate()
    {
        $this->cs->putInTree($this->crs->getRefId());
        $this->cs2->putInTree($this->cat->getRefId());

        $actions = $this->cs->getActions();
        $actions->markParentAsTemplate(self::TEMPLATE_PREFIX);

        $actions2 = $this->cs2->getActions();
        $actions2->markParentAsTemplate(self::TEMPLATE_PREFIX);

        $this->crs->read();
        $this->cat->read();
        $this->assertEquals(self::TEMPLATE_PREFIX . " TestCourse", $this->crs->getTitle());
        $this->assertEquals(self::TEMPLATE_PREFIX . " TestCategory", $this->cat->getTitle());
    }

    public function test_unmarkTemplate()
    {
        $this->cs->putInTree($this->crs->getRefId());
        $this->cs2->putInTree($this->cat->getRefId());

        $actions = $this->cs->getActions();
        $actions->markParentAsTemplate(self::TEMPLATE_PREFIX);
        $actions->unmarkParentAsTemplate(self::TEMPLATE_PREFIX);

        $actions2 = $this->cs2->getActions();
        $actions2->markParentAsTemplate(self::TEMPLATE_PREFIX);
        $actions2->unmarkParentAsTemplate(self::TEMPLATE_PREFIX);

        $this->crs->read();
        $this->cat->read();
        $this->assertEquals("TestCourse", $this->crs->getTitle());
        $this->assertEquals("TestCategory", $this->cat->getTitle());
    }
}
