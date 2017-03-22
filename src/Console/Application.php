<?php
/**
 * This file is part of the teamneusta/hosts project.
 * Copyright (c) 2017 neusta GmbH | Ein team neusta Unternehmen
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 *
 */

namespace TeamNeusta\Hosts\Console;

class Application extends \Symfony\Component\Console\Application
{
    /**
     * @var string
     */
    private $environment;

    protected static $_logo = <<<LOGO
    
               ,:',:`,:'
            __||_||_||_||__
       ____["""""""""""""""]____
       \ " '''''''''''''''''''' |
~^~^~^^~^~^~^~^~^~^~^~~^~^~^^~~^~^~^~^

LOGO;

    /**
     * @param string $name    The name of the application
     * @param string $version The version of the application
     */
    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN', $environment = 'prod')
    {
        $this->environment = $environment;
        parent::__construct($name, $version);
    }

    /**
     * @codeCoverageIgnore
     * @return string
     */
    public function getHelp() : string
    {
        return static::$_logo . parent::getHelp();
    }
}