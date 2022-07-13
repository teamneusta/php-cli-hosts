<?php
/**
 * This file is part of the teamneusta/codeception-docker-chrome package.
 * Copyright (c) 2017 neusta GmbH | Ein team neusta Unternehmen
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 *
 */

namespace TeamNeusta\Hosts\Test\Services\Validator;


use PHPUnit\Framework\TestCase;
use TeamNeusta\Hosts\Services\Validator\Scope;

class ScopeTest extends TestCase
{

    public function getScopesDataProvider()
    {
        return [
            'valid local scope' => [
                'local',
                false
            ],
            'valid project scope' => [
                'project',
                false
            ],
            'valid global scope' => [
                'global',
                false
            ],
            'valid NULL scope' => [
                null,
                false
            ],
            'invalid scope' => [
                'pim',
                true
            ],
        ];
    }

    /**
     * @test
     * @dataProvider getScopesDataProvider
     *
     * @return void
     */
    public function testValidateScopeWillThrowExceptionOnInvalidScopeValue($scope, $throwsException)
    {
        if ($throwsException) {
            $this->expectException(\InvalidArgumentException::class);
        }
        $result = Scope::validateScope($scope);

        $this->assertTrue($result);
    }
}