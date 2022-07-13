<?php
/**
 * This file is part of the teamneusta/hosts project.
 * Copyright (c) 2017 neusta GmbH | Ein team neusta Unternehmen
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 *
 */

namespace TeamNeusta\Hosts\Tests\Command;

use PHPUnit\Framework\TestCase;
use TeamNeusta\Hosts\Command\ConnectCommand;
use TeamNeusta\Hosts\Console\Application;
use TeamNeusta\Hosts\Services\HostService;
use TeamNeusta\Hosts\Services\Provider\Cli;
use Symfony\Component\Console\Tester\CommandTester;

class ConnectCommandTest extends TestCase
{

    /**
     * @var HostService | TestCase
     */
    private $hostServiceMock;

    /**
     * @var Cli
     */
    private $cliServiceMock;

    public function setUp(): void
    {
        parent::setUp();
        $this->hostServiceMock = $this->getMockBuilder("\\TeamNeusta\\Hosts\\Services\\HostService")
            ->disableOriginalConstructor()
            ->setMethods(['getHostsForQuestionhelper', 'getConnectionStringByName', 'getHosts'])
            ->getMock();

        $this->hostServiceMock->method('getHostsForQuestionhelper')
            ->willReturn([
                'SomeHost'
            ]);

        $this->cliServiceMock = $this->getMockBuilder('\\TeamNeusta\\Hosts\\Services\\Provider\\Cli')
            ->setMethods(['passthruSsh'])
            ->getMock();
    }

    /**
     * @test
     *
     * @return void
     */
    public function testConnectToHostWillCreateCliConnection()
    {
        $baseApplication = new Application("", "", 'dev');
        $baseApplication->add(new ConnectCommand(null, $this->hostServiceMock, $this->cliServiceMock));

        $command = $baseApplication->find('connect');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs([0]);
        $commandTester->execute(array(
            'command' => $command->getName(),
        ));

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString("You have selected: SomeHost", $output);
        $this->assertStringContainsString("establishing connection...", $output);
    }

    /**
     * @test
     *
     * @return void
     */
    public function testConnectToHostWillExitOnChoosingExitOption()
    {
        $baseApplication = new Application("", "", 'dev');
        $baseApplication->add(new ConnectCommand(null, $this->hostServiceMock, $this->cliServiceMock));

        $command = $baseApplication->find('connect');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['exit']);
        $commandTester->execute(array(
            'command' => $command->getName(),
        ));

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString("exiting.", $output);
        $this->assertStringContainsString("have a nice day :-)", $output);
    }

    /**
     * @test
     *
     * @return void
     */
    public function testInvalidHostWillRaiseErrorOfInvalidHost()
    {
        $this->markTestSkipped('This test is not supported on OSX.');

        $baseApplication = new Application("", "", 'dev');
        $baseApplication->add(new ConnectCommand(null, $this->hostServiceMock, $this->cliServiceMock));

        $command = $baseApplication->find('connect');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs([2]);
        $commandTester->execute(array(
            'command' => $command->getName(),
        ));

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString("Host #2 is invalid.", $output);
    }
}