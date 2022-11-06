<?php
/**
 * This file is part of the teamneusta/codeception-docker-chrome package.
 * Copyright (c) 2017 neusta GmbH | Ein team neusta Unternehmen
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 *
 */

namespace TeamNeusta\Hosts\Tests\Command;

use PHPUnit\Framework\TestCase;
use TeamNeusta\Hosts\Command\ListCommand;
use TeamNeusta\Hosts\Console\Application;
use TeamNeusta\Hosts\Services\HostService;
use Symfony\Component\Console\Tester\CommandTester;

class ListCommandTest extends TestCase
{

    /**
     * @var HostService | TestCase
     */
    private $hostServiceMock;

    public function setUp(): void
    {
        parent::setUp();
        $this->hostServiceMock = $this->getMockBuilder("\\TeamNeusta\\Hosts\\Services\\HostService")
            ->disableOriginalConstructor()
            ->setMethods(['getHosts'])
            ->getMock();
    }

    /**
     * @test
     *
     * @return void
     */
    public function testListHostWillReturnListOfAvailableHosts()
    {
        $this->hostServiceMock->method('getHosts')
            ->willReturn([
                [
                    'name' => 'SomeHost',
                    'host' => 'weired.host.tld',
                    'user' => 'jon.doe',
                    'scope' => 'local',
                ]
            ]);
        $baseApplication = new Application("", "", 'dev');
        $baseApplication->add(new ListCommand(null, $this->hostServiceMock));

        $command = $baseApplication->find('host:list');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs([0]);
        $commandTester->execute(array(
            'command' => $command->getName(),
        ));

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString(
            "| SomeHost | weired.host.tld | jon.doe | local |",
            $output
        );
    }

    /**
     * @test
     *
     * @return void
     */
    public function testListHostWillReturnNoticeOfNoHostsFound()
    {
        $this->hostServiceMock->method('getHosts')
            ->willReturn([]);

        $baseApplication = new Application("", "", 'dev');
        $baseApplication->add(new ListCommand(null, $this->hostServiceMock));

        $command = $baseApplication->find('host:list');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs([0]);
        $commandTester->execute(array(
            'command' => $command->getName(),
        ));

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString(
            "| No entries found.             |",
            $output
        );
    }
}