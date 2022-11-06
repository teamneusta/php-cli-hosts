<?php
/**
 * This file is part of the teamneusta/hosts project.
 * Copyright (c) 2017 neusta GmbH | Ein team neusta Unternehmen
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 *
 */

namespace TeamNeusta\Hosts\Command;

use Herrera\Phar\Update\Manager;
use Herrera\Phar\Update\Manifest;
use Herrera\Phar\Update\Update;
use Herrera\Version\Version;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UpdateCommand
 * Only utilizing a given Library.
 *
 * @codeCoverageIgnore
 *
 * @package Neusta\Hosts\Command
 */
class UpdateCommand extends Command
{
    /**
     * manifest.json Path to get Updated versions.
     */
    const MANIFEST_FILE = 'http://wamoco.github.io/php-cli-hosts/manifest.json';

    /**
     * Announce name and description so command could be called.
     */
    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('self-update')
            ->setAliases(['selfupdate'])
            // the short description shown while running "php bin/console list"
            ->setDescription('Update hosts.phar to latest version.');
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return null|int null or 0 if everything went fine, or an error code
     *
     * @throws LogicException When this abstract method is not implemented
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $manager = new Manager(Manifest::loadFile(self::MANIFEST_FILE));
        $updates = $manager->getManifest()->getUpdates();
        $latestUpdate = $this->getLatestUpdate($updates);
        $currentVersion = $this->getApplication()->getVersion();
        if ($latestUpdate->getVersion()->__toString() == $currentVersion) {
            $output->writeln('You got already the lastest Version: ' . $currentVersion);
            $output->writeln('nothing to do.');
            return 0;
        } else {
            $output->writeln('Updating to Version: ' . $latestUpdate->getVersion()->__toString());
        }
        $manager->update($this->getApplication()->getVersion(), true);
        $output->writeln('done.');
        return 0;
    }

    /**
     * @param Update[] $updates
     * @return Update
     */
    protected function getLatestUpdate($updates)
    {
        uasort($updates, function($a, $b){
           return version_compare($a->getVersion()->__toString(), $b->getVersion()->__toString(), 'lt');
        });

        return array_shift($updates);
    }
}