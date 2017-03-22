<?php
/**
 * This file is part of the teamneusta/hosts project.
 * Copyright (c) 2017 neusta GmbH | Ein team neusta Unternehmen
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 *
 */

namespace TeamNeusta\Hosts\Services\Validator;

class Scope
{
    const SCOPE_LOCAL = 'local';
    const SCOPE_GLOBAL = 'global';
    const SCOPE_PROJECT = 'project';

    public static function validateScope(string $scope = null)
    {
        $scopes = [
            self::SCOPE_GLOBAL,
            self::SCOPE_LOCAL,
            self::SCOPE_PROJECT
        ];

        if (!in_array($scope, $scopes) && $scope !== null) {
            throw new \InvalidArgumentException(printf('Scope "%s" not defined.', $scope));
        }
    }
}