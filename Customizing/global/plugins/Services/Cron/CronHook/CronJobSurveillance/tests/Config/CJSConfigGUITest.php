<?php
namespace CaT\Plugins\CronJobSurveillance\Config;

use PHPUnit\Framework\TestCase;

/**
 * Test for the class CJSConfigGUI.
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class CJSConfigGUITest extends TestCase
{
    public function testExecuteCommandWithShowConfig()
    {
        $gui = $this
            ->getMockBuilder(CJSConfigGUI::class)
            ->setMethods([
                "getCommand",
                "setContent",
                "getPost",
                "show"
            ])
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $gui
            ->expects($this->once())
            ->method("getCommand")
            ->willReturn(CJSConfigGUI::CMD_SHOW_CONFIG)
        ;

        $gui
            ->expects($this->once())
            ->method("show")
        ;

        $gui->executeCommand();
    }

    public function testExecuteCommandWithSaveConfig()
    {
        $gui = $this
            ->getMockBuilder(CJSConfigGUI::class)
            ->setMethods([
                "getCommand",
                "setContent",
                "getPost",
                "save"
            ])
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $gui
            ->expects($this->once())
            ->method("getCommand")
            ->willReturn(CJSConfigGUI::CMD_SAVE_CONFIG)
        ;

        $gui
            ->expects($this->once())
            ->method("save")
        ;

        $gui->executeCommand();
    }

    public function testExecuteCommandWithCancel()
    {
        $gui = $this
            ->getMockBuilder(CJSConfigGUI::class)
            ->setMethods([
                "getCommand",
                "setContent",
                "getPost",
                "cancel"
            ])
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $gui
            ->expects($this->once())
            ->method("getCommand")
            ->willReturn(CJSConfigGUI::CMD_CANCEL_CONFIG)
        ;

        $gui
            ->expects($this->once())
            ->method("cancel")
        ;

        $gui->executeCommand();
    }

    public function testExecuteCommandWithUnknownCommand()
    {
        $gui = $this
            ->getMockBuilder(CJSConfigGUI::class)
            ->setMethods([
                "getCommand",
                "setContent",
                "getPost"
            ])
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $gui
            ->expects($this->once())
            ->method("getCommand")
            ->willReturn("unknown_command")
        ;

        try {
            $gui->executeCommand();
            die("testExecuteCommandWithUnknownCommand should throw an Exception.");
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
    }

    public function testShow()
    {
        $fb = $this
            ->getMockBuilder(FormBuilder::class)
            ->getMock()
        ;

        $cf = $this
            ->getMockBuilder(ConfigurationForm::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $db = $this
            ->getMockBuilder(DB::class)
            ->setMethods([
                "select",
                "create",
                "selectForJob",
                "deleteForJob",
                "deleteAll"
            ])
            ->getMock()
        ;

        $db
            ->expects($this->once())
            ->method("select")
            ->willReturn(array(new JobSetting("test", 33)))
        ;

        $gui = $this
            ->getMockBuilder(CJSConfigGUI::class)
            ->setMethods([
                "getCommand",
                "setContent",
                "getPost",
                "getForm"
            ])
            ->setConstructorArgs(array($fb, $cf, $db, function () {
                return "test";
            }))
            ->getMock()
        ;

        $form = $this
            ->getMockBuilder(FormWrapper::class)
            ->setMethods([
                "getHtml",
                "setInputByArray",
                "checkInput"
            ])
            ->getMock()
        ;

        $gui
            ->expects($this->once())
            ->method("getForm")
            ->willReturn($form)
        ;

        $form
            ->expects($this->once())
            ->method("getHtml")
            ->willReturn("<h1>Erfolg</h1>")
        ;

        $gui->show();
    }

    public function testSave()
    {
        $fb = $this
            ->getMockBuilder(FormBuilder::class)
            ->getMock()
        ;

        $cf = $this
            ->getMockBuilder(ConfigurationForm::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $db = $this
            ->getMockBuilder(DB::class)
            ->setMethods([
                "select",
                "create",
                "selectForJob",
                "deleteForJob",
                "deleteAll"
            ])
            ->getMock()
        ;

        $db
            ->expects($this->once())
            ->method("deleteAll")
        ;

        $db
            ->expects($this->any())
            ->method("create")
        ;

        $form = $this
            ->getMockBuilder(FormWrapper::class)
            ->setMethods([
                "getHtml",
                "setInputByArray",
                "checkInput"
            ])
            ->getMock()
        ;


        $form
            ->expects($this->once())
            ->method("checkInput")
            ->willReturn(true)
        ;

        $gui = $this
            ->getMockBuilder(CJSConfigGUI::class)
            ->setMethods([
                "getCommand",
                "setContent",
                "getPost",
                "getForm",
                "show"
            ])
            ->setConstructorArgs(array($fb, $cf, $db, function () {
                return "test";
            }))
            ->getMock()
        ;

        $gui
            ->expects($this->once())
            ->method("getForm")
            ->willReturn($form)
        ;

        $gui
            ->expects($this->once())
            ->method("getPost")
            ->willReturn(array("job_id" => "test", "tolerance" => 33))
        ;

        $gui
            ->expects($this->once())
            ->method("show")
        ;

        $_POST = array("one", "two");
        $gui->save();
    }

    public function testCancel()
    {
        $gui = $this
            ->getMockBuilder(CJSConfigGUI::class)
            ->setMethods([
                "getCommand",
                "setContent",
                "getPost",
                "show"
            ])
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $gui
            ->expects($this->once())
            ->method("show")
        ;

        $gui->cancel();
    }

    public function testGetForm()
    {
        $form = $this
            ->getMockBuilder(FormWrapper::class)
            ->getMock()
        ;

        $configuration_form = $this
            ->getMockBuilder(ConfigurationForm::class)
            ->disableOriginalConstructor()
            ->setMethods(['build'])
            ->getMock()
        ;

        $form_builder = $this
            ->getMockBuilder(FormBuilder::class)
            ->setMethods([
                "getForm",
                "addCJSConfigHeaderGUI",
                "addCJSConfigItemGUI",
                "addButton"
            ])
            ->getMock()
        ;

        $configuration_form
            ->expects($this->once())
            ->method("build")
            ->with($form_builder)
            ->willReturn($form)
        ;

        $db = $this
            ->getMockBuilder(DB::class)
            ->setMethods([
                "select",
                "create",
                "selectForJob",
                "deleteForJob",
                "deleteAll"
            ])
            ->getMock()
        ;

        $gui = $this
            ->getMockBuilder(CJSConfigGUI::class)
            ->setConstructorArgs([
                $form_builder,
                $configuration_form,
                $db,
                function () {
                    return "";
                }
            ])
            ->setMethods([
                "getCommand",
                "setContent",
                "getPost"
            ])
            ->getMock()
        ;

        $form = $gui->getForm();

        $this->assertInstanceOf(FormWrapper::class, $form);
    }
}
