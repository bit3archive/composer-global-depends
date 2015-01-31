<?php

/**
 * This file is part of bit3/composer-global-depends.
 *
 * (c) 2015 Tristan Lins
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    bit3/composer-global-depends
 * @author     Tristan Lins <tristan@lins.io>
 * @copyright  Tristan Lins <tristan@lins.io>
 * @link       https://github.com/bit3/composer-global-depends
 * @license    https://github.com/bit3/composer-global-depends/blob/master/LICENSE MIT
 * @filesource
 */

namespace Bit3\ComposerGlobalDepends\Command;

use Bit3\ComposerGlobalDepends\DependencyAnalyser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Analyse dependencies globally.
 */
class GlobalDependsCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('composer:global-depends')
            ->setDescription('Search all dependencies for the given package.')
            ->addOption('devs', 'd', InputOption::VALUE_NONE, 'Search in all dev-x and x-dev releases.')
            ->addOption('releases', 'r', InputOption::VALUE_NONE, 'Search in all tagged releases.')
            ->addArgument('package', InputArgument::REQUIRED, 'The dependend package name.');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $devs        = $input->getOption('devs');
        $releases    = $input->getOption('releases');
        $packageName = $input->getArgument('package');

        if (!$devs && !$releases) {
            // no one is selected, fall back to dev releases
            $devs = true;
        }

        $analyser = new DependencyAnalyser($packageName, $this->getApplication(), $input, $output);
        $analyser->setSearchInDevReleases($devs);
        $analyser->setSearchInReleases($releases);
        $analyser->run();
    }
}
