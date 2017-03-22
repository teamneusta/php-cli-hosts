<?php
/**
 * This file is part of the teamneusta/hosts project.
 * Copyright (c) 2017 neusta GmbH | Ein team neusta Unternehmen
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 *
 */

namespace TeamNeusta\Hosts\Tests\Command;

use TeamNeusta\Hosts\Command\AddCommand;
use TeamNeusta\Hosts\Console\Application;
use TeamNeusta\Hosts\Services\HostService;
use Symfony\Component\Console\Tester\ApplicationTester;
use Symfony\Component\Console\Tester\CommandTester;

class AddCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HostService | \PHPUnit_Framework_MockObject_MockObject
     */
    private $hostServiceMock;

    public function setUp()
    {
        parent::setUp();
        $this->hostServiceMock = $this->getMockBuilder("\\TeamNeusta\\Hosts\\Services\\HostService")
            ->disableOriginalConstructor()
            ->setMethods(['getHostsForQuestionhelper', 'getConnectionStringByName','setScope','update'])
            ->getMock();

        $this->hostServiceMock->method('getHostsForQuestionhelper')
            ->willReturn([
                'SomeHost'
            ]);
    }

    public function getEnvironmentDataProvider()
    {
        return [
            'project scope' => [1, 'project'],
            'local scope' => [0, 'local']
        ];
    }

    /**
     * @test
     * @dataProvider getEnvironmentDataProvider
     *
     * @return void
     */
    public function testAddWillAddHostAndReturnAddedHost($parameter, $expectation)
    {
        $baseApplication = new Application(null, null, 'dev');
        $baseApplication->add(new AddCommand(null, $this->hostServiceMock));

        $command = $baseApplication->find('host:add');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['Test Host', 'some.weired.host', 'username', 22, $parameter]);
        $commandTester->execute(array(
            'command' => $command->getName(),
        ));

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertContains('Added Entry: username@some.weired.host for ' . $expectation . ' scope.', $output);
    }
}