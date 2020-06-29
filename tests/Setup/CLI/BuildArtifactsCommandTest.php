<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup\CLI;

use ILIAS\Setup;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class BuildArtifactsCommandTest extends TestCase
{
    public function testBasicFunctionality() : void
    {
        $agent = $this->createMock(Setup\Agent::class);
        $agent_finder = $this->createMock(Setup\ImplementationOfAgentFinder::class);
        $config_reader = $this->createMock(Setup\CLI\ConfigReader::class);
        $command = new Setup\CLI\BuildArtifactsCommand($agent_finder,$config_reader, []);
        $tester = new CommandTester($command);

        $objective = $this->createMock(Setup\Objective::class);
        $env = $this->createMock(Setup\Environment::class);

        $agent_finder
            ->expects($this->once())
            ->method('buildAgentCollection')
            ->willReturn($agent)
        ;

        $agent
            ->expects($this->once())
            ->method("getBuildArtifactObjective")
            ->with()
            ->willReturn($objective);

        $objective
            ->expects($this->once())
            ->method("getPreconditions")
            ->willReturn([]);

        $objective
            ->expects($this->once())
            ->method("achieve")
            ->willReturn($env);

        $objective
            ->expects($this->once())
            ->method("isApplicable")
            ->willReturn(true);

        $tester->execute([
        ]);
    }
}
