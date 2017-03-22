<?php
/**
 * This file is part of the teamneusta/hosts project.
 * Copyright (c) 2017 neusta GmbH | Ein team neusta Unternehmen
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 *
 */

namespace TeamNeusta\Hosts\Services\Provider;

/**
 * Class Cli
 * Passing by a php method for testing purposes.
 *
 * @codeCoverageIgnore
 * @package TeamNeusta\Hosts\Services\Provider
 */
class Cli
{
    public function passthruSsh($string)
    {
        return passthru("ssh " . $string);
    }
}