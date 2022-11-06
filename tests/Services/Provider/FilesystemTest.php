<?php
/**
 * This file is part of the teamneusta/hosts project.
 * Copyright (c) 2017 neusta GmbH | Ein team neusta Unternehmen
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 *
 */

namespace TeamNeusta\Hosts\Tests\Services\Provider;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TeamNeusta\Hosts\Services\Provider\Filesystem;

/**
 * Class FilesystemTest
 *
 * @package Neusta\Hosts\Test\Services\Provider
 */
class FilesystemTest extends TestCase
{
    /**
     * @var \Symfony\Component\Filesystem\Filesystem | MockObject
     */
    private $fileSystem;

    /**
     * @var \TeamNeusta\Hosts\Services\Provider\File | MockObject
     */
    private $file;

    /**
     * @var string
     */
    private $originalHomePath;

    public function setUp(): void
    {
        $this->originalHomePath = getenv('HOME');
        parent::setUp();

        putenv('HOME=/some/home/path/');

        $this->fileSystem = $this->getMockBuilder("\\Symfony\\Component\\Filesystem\\Filesystem")
            ->disableOriginalConstructor()
            ->onlyMethods(['exists', 'dumpFile'])
            ->getMock();
        $this->file = $this->getMockBuilder("\\TeamNeusta\\Hosts\\Services\\Provider\\File")
            ->disableOriginalConstructor()
            ->onlyMethods(['getContents'])
            ->getMock();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        putenv("HOME=" . $this->originalHomePath);
    }

    public function testGetHomeDirWillRelyOnServerIfEnvironmentIsMissing()
    {
        putenv("HOME=");
        $_SERVER['HOMEPATH'] = 'homePath/';
        $_SERVER['HOMEDRIVE'] = '/homeDrive/';

        $filesystem = new Filesystem($this->fileSystem, $this->file);

        $result = $filesystem->getHomeDir();

        $this->assertSame('/homeDrive/homePath', $result);
    }

    /**
     * @test
     *
     * @return void
     */
    public function testGetConfigurationFileWillCreateDefaultFileIfNotExist()
    {
        $this->fileSystem->expects($this->once())
            ->method('exists')
            ->willReturn(false);
        $this->fileSystem->expects($this->once())
            ->method('dumpFile')
            ->with(
                'someFile.txt',
                json_encode(['hosts' => []])
            );
        $this->file->expects($this->once())
            ->method('getContents')
            ->with('someFile.txt')
            ->willReturn(json_encode([]));

        $filesystem = new Filesystem($this->fileSystem, $this->file);

        $result = $filesystem->getConfiguration('someFile.txt', true);

        $this->assertSame([], $result);
    }

    /**
     * @test
     *
     * @return void
     */
    public function testGetConfigurationFileWillReturnFalseOnJsonDecodeIssue()
    {
        $this->fileSystem->expects($this->once())
            ->method('exists')
            ->willReturn(false);

        $this->fileSystem->expects($this->once())
            ->method('dumpFile')
            ->with(
                'someFile.txt',
                json_encode(['hosts' => []])
            );

        $this->file->expects($this->once())
            ->method('getContents')
            ->with('someFile.txt')
            ->willReturn('{noneJsonString]');

        $filesystem = new Filesystem($this->fileSystem, $this->file);

        $result = $filesystem->getConfiguration('someFile.txt', true);

        $this->assertSame([],$result);
    }

    /**
     * @test
     * @return void
     */
    public function testGetConfigurationFileWillThrowExceptionOnErrorInFileDumping()
    {
        $this->expectException(\IOException::class);

        $this->fileSystem->expects($this->once())
            ->method('exists')
            ->willReturn(false);
        $this->fileSystem->expects($this->once())
            ->method('dumpFile')
            ->with(
                'someFile.txt',
                json_encode(['hosts' => []])
            )
        ->willThrowException(new \Exception('Fehler'));

        $filesystem = new Filesystem($this->fileSystem, $this->file);

        $result = $filesystem->getConfiguration('someFile.txt', true);

        $this->assertFalse($result);
    }

    /**
     * @test
     *
     * @return void
     */
    public function testGetLocalConfigurationWillReturnEntriesWithScopeValueSet()
    {
        $this->fileSystem->expects($this->once())
            ->method('exists')
            ->willReturn(false);
        $this->fileSystem->expects($this->once())
            ->method('dumpFile')
            ->with(
                '/some/home/path'.DIRECTORY_SEPARATOR.'.hosts',
                json_encode(['hosts' => []])
            );
        $this->file->expects($this->once())
            ->method('getContents')
            ->with('/some/home/path'.DIRECTORY_SEPARATOR.'.hosts')
            ->willReturn(json_encode(['hosts' => [['name' => 'HostName']]]));

        $filesystem = new Filesystem($this->fileSystem, $this->file);

        $result = $filesystem->getLocalConfiguration();

        $this->assertSame(['hosts' => [['name' => 'HostName', 'scope' => 'local']]], $result);
    }

    /**
     * @test
     *
     * @return void
     */
    public function testGetProjectConfigurationWillReturnEntriesWithScopeValueSet()
    {
        $this->fileSystem->expects($this->once())
            ->method('exists')
            ->willReturn(false);
        $this->fileSystem->expects($this->never())
            ->method('dumpFile');
        $this->file->expects($this->once())
            ->method('getContents')
            ->with('.'.DIRECTORY_SEPARATOR.'.hosts')
            ->willReturn(json_encode(['hosts' => [['name' => 'HostName']]]));

        $filesystem = new Filesystem($this->fileSystem, $this->file);

        $result = $filesystem->getProjectConfiguration();

        $this->assertSame(['hosts' => [['name' => 'HostName', 'scope' => 'project']]], $result);
    }

    /**
     * @test
     *
     * @return void
     */
    public function testGetGlobalConfigurationWillReturnEntriesWithScopeValueSet()
    {
        $this->markTestSkipped("Not sure how to fix test depending on non existing value from config.");

        $this->fileSystem->expects($this->once())
            ->method('exists')
            ->willReturn(false);

        $this->fileSystem->expects($this->never())
            ->method('dumpFile');
        $this->file->expects($this->once())
            ->method('getContents')
            ->with('http://127.0.0.1:8080/hosts/hosts.json')
            ->willReturn(json_encode([['name' => 'HostName']]));

        $filesystem = new Filesystem($this->fileSystem, $this->file);

        $result = $filesystem->getGlobalConfiguration();

        $this->assertSame(['hosts' => ['name' => 'HostName', 'scope' => 'global']], $result);
    }

    public function getScopePathsAndValuesDataProvider()
    {
        return [
            'local/default Scope' => [
                'local',
                '/some/home/path'.DIRECTORY_SEPARATOR.'.hosts',
                ['hosts' => [[
                    'name' => 'HostName',
                    'host' => '',
                    'user' => '',
                    'port' => 22
                ]]]
            ],
            'project Scope' => [
                'project',
                '.'.DIRECTORY_SEPARATOR.'.hosts',
                ['hosts' => [[
                    'name' => 'HostName',
                    'host' => '',
                    'user' => '',
                    'port' => 22
                ]]]
            ]
        ];
    }

    /**
     * @test
     * @dataProvider getScopePathsAndValuesDataProvider
     *
     * @return void
     */
    public function testAddHostToConfigurationWillAddHostDependingOnGivenScope($scope, $expectedFileName, $expectedData)
    {
        $this->fileSystem
            ->method('exists')
            ->willReturn(true);

        $this->fileSystem->expects($this->once())
            ->method('dumpFile')
            ->with(
                $expectedFileName,
                json_encode($expectedData)
            );

        $this->file->expects($this->once())
            ->method('getContents')
            ->with($expectedFileName)
            ->willReturn(json_encode(['hosts' => []]));

        $filesystem = new Filesystem($this->fileSystem, $this->file);

        $filesystem->addHostToConfiguration(['name' => 'HostName'], $scope);
    }

    /**
     * @test
     *
     * @return void
     */
    public function testAddHostToConfigurationWillPassExceptionWithCatchedMassage()
    {
        $this->fileSystem
            ->method('exists')
            ->willReturn(true);

        $this->fileSystem->expects($this->any())
            ->method('dumpFile')
            ->with(
                '/some/home/path'.DIRECTORY_SEPARATOR.'.hosts',
                json_encode(['hosts' => [[
                    'name' => 'HostName',
                    'host' => '',
                    'user' => '',
                    'port' => 22
                ]]])
            )
            ->willThrowException(new \Exception('SomeError'));
        $this->file->expects($this->any())
            ->method('getContents')
            ->with('/some/home/path'.DIRECTORY_SEPARATOR.'.hosts')
            ->willReturn(json_encode(['hosts' => []]));

        $this->expectException("Exception");
        $this->expectExceptionMessage('SomeError');

        $filesystem = new Filesystem($this->fileSystem, $this->file);

        $filesystem->addHostToConfiguration(['name' => 'HostName']);
    }

    /**
     * @test
     *
     * @return void
     */
    public function testSetGlobalHostsUrlWillAddGivenUrlToConfigFile()
    {
        $this->fileSystem
            ->method('exists')
            ->willReturn(true);

        $this->file->expects($this->once())
            ->method('getContents')
            ->with('/some/home/path'.DIRECTORY_SEPARATOR.'.hosts')
            ->willReturn(json_encode(['hosts' => []]));

        $this->fileSystem->expects($this->once())
            ->method('dumpFile')
            ->with('/some/home/path'.DIRECTORY_SEPARATOR.'.hosts', '{"hosts":[],"hosts_url":"someHost"}');

        $filesystem = new Filesystem($this->fileSystem, $this->file);

        $filesystem->setGlobalHostsUrl('someHost');
    }

    /**
     * @test
     *
     * @return void
     */
    public function testSetGlobalHostsUrlWillThrowExceptionIfOverrideIsSetToFalseAndHostAlreadyExist()
    {
        $this->fileSystem
            ->method('exists')
            ->willReturn(true);

        $this->file->expects($this->any())
            ->method('getContents')
            ->with('/some/home/path'.DIRECTORY_SEPARATOR.'.hosts')
            ->willReturn(json_encode(['hosts_url' => 'someHost', 'hosts' => []]));

        $this->expectException("\\TeamNeusta\\Hosts\\Exception\\HostAlreadySetException");

        $filesystem = new Filesystem($this->fileSystem, $this->file);

        $filesystem->setGlobalHostsUrl('someHost');
    }

    /**
     * @test
     *
     * @return void
     */
    public function testSetGlobalHostsUrlWillAddGivenUrlToConfigFile_EvenIfExistWhenPassingOverrideOption()
    {
        $this->fileSystem
            ->method('exists')
            ->willReturn(true);

        $this->file->expects($this->once())
            ->method('getContents')
            ->with('/some/home/path'.DIRECTORY_SEPARATOR.'.hosts')
            ->willReturn(json_encode(["hosts_url" => "someHost", 'hosts' => []]));

        $this->fileSystem->expects($this->once())
            ->method('dumpFile')
            ->with('/some/home/path'.DIRECTORY_SEPARATOR.'.hosts', '{"hosts_url":"someHost","hosts":[]}');

        $filesystem = new Filesystem($this->fileSystem, $this->file);

        $filesystem->setGlobalHostsUrl('someHost', true);
    }

    public function testGetGlobalConfigurationWillReturnConfigArrayIfPathIsSet()
    {
        $this->fileSystem
            ->method('exists')
            ->willReturn(true);

        $this->file->expects($this->any())
            ->method('getContents')
            ->withConsecutive(
                [
                    '/some/home/path'.DIRECTORY_SEPARATOR.'.hosts',
                ]
            )
            ->willReturn(json_encode(["hosts_url" => "someHost", 'hosts' => [['host' => 'someHost']]]));

        $this->fileSystem->expects($this->any())
            ->method('dumpFile')
            ->with('/some/home/path'.DIRECTORY_SEPARATOR.'.hosts', '{"hosts_url":"someHost","hosts":[]}');

        $filesystem = new Filesystem($this->fileSystem, $this->file);

        $result = $filesystem->getGlobalConfiguration();

        $this->assertSame(["hosts_url" => "someHost", 'hosts' => [['host' => 'someHost', 'scope' => 'global']]], $result);
    }

    public function testGetGlobalConfigurationWillReturnEmptyConfigArrayIfHostUrlNotFoundInConfugration()
    {
        $this->fileSystem
            ->method('exists')
            ->willReturn(true);

        $this->file->expects($this->any())
            ->method('getContents')
            ->withConsecutive(
                [
                    '/some/home/path'.DIRECTORY_SEPARATOR.'.hosts',
                ]
            )
            ->willReturnOnConsecutiveCalls(
                    json_encode(['hosts' => [['host' => 'someHost']]]),
                    '[]'
            );

        $this->fileSystem->expects($this->any())
            ->method('dumpFile')
            ->with('/some/home/path'.DIRECTORY_SEPARATOR.'.hosts', '{"hosts_url":"someHost","hosts":[]}');

        $filesystem = new Filesystem($this->fileSystem, $this->file);

        $result = $filesystem->getGlobalConfiguration();

        $this->assertSame([], $result);
    }

    /**
     * @test
     * @return void
     */
    public function addScopeWillReturnEmptyConfigWithHostsValueOnNonArrayConfigParameter()
    {
        $filesystem = new Filesystem($this->fileSystem, $this->file);

        $result = $filesystem->addScope(null,null);

        self::assertSame(['hosts' => []], $result);
    }

    /**
     * @test
     * @return void
     */
    public function addScopeWillNotModifyConfigParameterIfHostsKeyIsNotSet()
    {
        $config = [];
        $filesystem = new Filesystem($this->fileSystem, $this->file);

        $result = $filesystem->addScope([], 'foobar');

        self::assertSame($config, $result);
    }
}