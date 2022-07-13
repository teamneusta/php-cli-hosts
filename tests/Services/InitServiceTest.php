<?php
/**
 * This file is part of the teamneusta/codeception-docker-chrome package.
 * Copyright (c) 2017 neusta GmbH | Ein team neusta Unternehmen
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 *
 */

namespace TeamNeusta\Hosts\Tests\Services;

use PHPUnit\Framework\TestCase;
use TeamNeusta\Hosts\Services\InitService;
use TeamNeusta\Hosts\Services\Provider\Filesystem;
use org\bovigo\vfs\vfsStream;

class InitServiceTest extends TestCase
{
    /**
     * @var \TeamNeusta\Hosts\Services\Provider\Filesystem | \PHPUnit_Framework_MockObject_MockObject
     */
    private $fileSystemMock;

    /**
     * Public setUp.
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->fileSystemMock = $this->getMockBuilder("\\TeamNeusta\\Hosts\\Services\\Provider\\Filesystem")
            ->disableOriginalConstructor()
            ->setMethods([
                'getHomeDir',
            ])
            ->getMock();
    }

    /**
     * @test
     *
     * @return void
     */
    public function validateLocalConfigurationExistAndReturnTrue()
    {
        $root = vfsStream::setup('exampleDir');
        vfsStream::newFile(Filesystem::CONFIGURATION_FILE_NAME)->at($root)->setContent('');

        $this->fileSystemMock->method('getHomeDir')
            ->willReturn($root->url());

        $initService = new InitService($this->fileSystemMock);

        self::assertTrue($initService->localConfigurationExist());
    }

    /**
     * @test
     *
     * @return void
     */
    public function validateLocalConfigurationExistAndReturnFalse()
    {
        $root = vfsStream::setup('exampleDir');
        vfsStream::newFile('missing File')->at($root)->setContent('');

        $this->fileSystemMock->method('getHomeDir')
            ->willReturn($root->url());

        $initService = new InitService($this->fileSystemMock);

        self::assertFalse($initService->localConfigurationExist());
    }
}