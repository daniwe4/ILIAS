<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup\CLI;

use ILIAS\Setup;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;

class AchieveCommandTest extends TestCase
{
    /**
     * @var Setup\CLI\ConfigReader|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $config_reader;

    /**
     * @var Setup\AgentFinder|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $agent_finder;

    /**
     * @var Setup\CLI\AchieveCommand
     */
    protected $command;

    public function setUp() : void
    {
        $this->config_reader = $this->createMock(Setup\CLI\ConfigReader::class);
        $this->agent_finder = $this->createMock(Setup\AgentFinder::class);
        $this->command = new Setup\CLI\AchieveCommand($this->agent_finder, $this->config_reader, []);
    }

    public function testExecuteWithWrongFormattedCommandString() : void
    {
        $wrong_command = "ilTest:Method";

        $tester = new CommandTester($this->command);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Wrong input format for '" . $wrong_command ."'.");
        $tester->execute([
            "agent_method" => $wrong_command
        ]);
    }

    public function testExecuteWithNullObjectives() : void
    {
        $agent = $this->createMock(Setup\AgentCollection::class);

        $command = "ilTest::Method";

        $this->agent_finder
            ->expects($this->once())
            ->method("getAgentForMethodCall")
            ->with("ilTest")
            ->willReturn($agent)
        ;

        $agent
            ->expects($this->once())
            ->method("hasConfig")
            ->willReturn(false)
        ;
        $agent
            ->expects($this->once())
            ->method("getAchieveObjectives")
            ->willReturn(null)
        ;

        $tester = new CommandTester($this->command);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Method 'Method' not found for 'ilTest'.");
        $tester->execute([
            "agent_method" => $command
        ]);
    }

    public function testExecuteWithoutConfig() : void
    {
        $agent = $this->createMock(Setup\AgentCollection::class);
        $objective = $this->createMock(Setup\Objective::class);
        $env = $this->createMock(Setup\Environment::class);

        $command = "ilTest::Method";

        $this->agent_finder
            ->expects($this->once())
            ->method("getAgentForMethodCall")
            ->with("ilTest")
            ->willReturn($agent)
        ;

        $agent
            ->expects($this->once())
            ->method("hasConfig")
            ->willReturn(false)
        ;
        $agent
            ->expects($this->once())
            ->method("getAchieveObjectives")
            ->willReturn($objective)
        ;

        $objective
            ->expects($this->once())
            ->method("getPreconditions")
            ->willReturn([])
        ;
        $objective
            ->expects($this->once())
            ->method("achieve")
            ->willReturn($env)
        ;
        $objective
            ->expects($this->once())
            ->method("isApplicable")
            ->willReturn(true)
        ;

        $tester = new CommandTester($this->command);
        $tester->execute([
            "agent_method" => $command
        ]);
    }

    public function testExecuteWithConfig() : void
    {
        $refinery = new Refinery($this->createMock(DataFactory::class), $this->createMock(\ilLanguage::class));
        $agent = $this->createMock(Setup\AgentCollection::class);
        $objective = $this->createMock(Setup\Objective::class);
        $env = $this->createMock(Setup\Environment::class);
        $config = $this->createMock(Setup\ConfigCollection::class);

        $command = "ilTest::Method";
        $config_file = "config_file";
        $config_file_content = ["config_file"];

        $this->config_reader
            ->expects($this->once())
            ->method("readConfigFile")
            ->with($config_file)
            ->willReturn($config_file_content)
        ;

        $this->agent_finder
            ->expects($this->once())
            ->method("getAgentForMethodCall")
            ->with("ilTest")
            ->willReturn($agent)
        ;

        $agent
            ->expects($this->once())
            ->method("hasConfig")
            ->willReturn(true)
        ;
        $agent
            ->expects($this->once())
            ->method("getArrayToConfigTransformation")
            ->with()
            ->willReturn($refinery->custom()->transformation(function ($v) use ($config_file_content, $config) {
                $this->assertEquals($v, $config_file_content);
                return $config;
            }))
        ;
        $agent
            ->expects($this->once())
            ->method("getAchieveObjectives")
            ->with($config, 'Method')
            ->willReturn($objective)
        ;

        $objective
            ->expects($this->once())
            ->method("getPreconditions")
            ->willReturn([])
        ;
        $objective
            ->expects($this->once())
            ->method("achieve")
            ->willReturn($env)
        ;
        $objective
            ->expects($this->once())
            ->method("isApplicable")
            ->willReturn(true)
        ;

        $tester = new CommandTester($this->command);
        $tester->execute([
            "agent_method" => $command,
            "config" => $config_file
        ]);
    }
}
