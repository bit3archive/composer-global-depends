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

namespace Bit3\ComposerGlobalDepends;

use Composer\Composer;
use Composer\Factory;
use Composer\IO\BufferIO;
use Composer\Package\AliasPackage;
use Composer\Package\Link;
use Composer\Package\PackageInterface;
use Composer\Repository\ComposerRepository;
use Composer\Repository\RepositoryInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Analyse dependencies globally.
 *
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
class DependencyAnalyser
{
    /**
     * The searched package name.
     *
     * @var string
     */
    private $packageName;

    /**
     * Search in dev releases (branches).
     *
     * @var bool
     */
    private $searchInDevReleases = false;

    /**
     * Search in releases (tags).
     *
     * @var bool
     */
    private $searchInReleases = false;

    /**
     * The console application.
     *
     * @var Application
     */
    private $application;

    /**
     * The console input.
     *
     * @var InputInterface
     */
    private $input;

    /**
     * The console output.
     *
     * @var OutputInterface
     */
    private $output;

    /**
     * The formatter helper.
     *
     * @var FormatterHelper $formatter
     */
    private $formatter;

    /**
     * The progress bar.
     *
     * @var ProgressBar
     */
    private $progress;

    /**
     * The composer factory.
     *
     * @var Factory
     */
    private $factory;

    /**
     * The composer input/output.
     *
     * @var BufferIO
     */
    private $io;

    /**
     * The composer instance.
     *
     * @var Composer
     */
    private $composer;

    /**
     * Create new analyser.
     *
     * @param string          $packageName The package name.
     * @param Application     $application The console application.
     * @param InputInterface  $input       The console input.
     * @param OutputInterface $output      The console output.
     */
    public function __construct($packageName, Application $application, InputInterface $input, OutputInterface $output)
    {
        $this->packageName = $packageName;
        $this->application = $application;
        $this->input       = $input;
        $this->output      = $output;
    }

    /**
     * Return the searched package name.
     *
     * @return string
     */
    public function getPackageName()
    {
        return $this->packageName;
    }

    /**
     * Determine if dev releases (branches) should be searched for dependencies.
     *
     * @return boolean
     */
    public function isSearchInDevReleases()
    {
        return $this->searchInDevReleases;
    }

    /**
     * Set if dev releases (branches) should be searched for dependencies.
     *
     * @param boolean $searchInDevReleases Search in dev releases.
     *
     * @return static
     */
    public function setSearchInDevReleases($searchInDevReleases)
    {
        $this->searchInDevReleases = (bool) $searchInDevReleases;
        return $this;
    }

    /**
     * Determine if releases (tags) should be searched for dependencies.
     *
     * @return boolean
     */
    public function isSearchInReleases()
    {
        return $this->searchInReleases;
    }

    /**
     * Set if releases (tags) should be searched for dependencies.
     *
     * @param boolean $searchInReleases Search in releases.
     *
     * @return static
     */
    public function setSearchInReleases($searchInReleases)
    {
        $this->searchInReleases = (bool) $searchInReleases;
        return $this;
    }

    /**
     * Run the dependency analysis.
     *
     * @return void
     */
    public function run()
    {
        $this->setUp();

        $this->progress->start();

        try {
            $this->search();

            $this->progress->finish();
        } catch (\Exception $e) {
            $this->progress->finish();
            $this->application->renderException($e, $this->output);

            if ($this->output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                $this->formatter->formatBlock($this->io->getOutput(), 'error');
            }
        }
    }

    /**
     * Set up all required helpers and the composer library.
     *
     * @return void
     */
    private function setUp()
    {
        $this->formatter = $this->application->getHelperSet()->get('formatter');

        $this->progress = new ProgressBar($this->output);
        $this->progress->setFormat('%current:6d% [%bar%] %elapsed%   %message%');
        $this->progress->setMessage('');

        $this->factory  = new Factory();
        $this->io       = new BufferIO();
        $this->composer = $this->factory->createComposer($this->io);
    }

    /**
     * Search for dependencies in all repositories.
     *
     * @return void
     */
    private function search()
    {
        /** @var RepositoryInterface[] $repositories */
        $repositories = $this->composer->getRepositoryManager()->getRepositories();

        foreach ($repositories as $repository) {
            $this->searchInRepository($repository);
        }
    }

    /**
     * Search for dependencies in this repository.
     *
     * @param RepositoryInterface $repository The repository to search in.
     *
     * @return void
     */
    private function searchInRepository(RepositoryInterface $repository)
    {
        if ($repository instanceof ComposerRepository) {
            $packageNames = $repository->getProviderNames();
            $this->searchInPackageNames($packageNames, $repository);
        } else {
            $packages = $repository->getPackages();
            $this->searchInPackages($packages);
        }
    }

    /**
     * Search for dependencies in multiple packages.
     *
     * @param string[]           $packageNames The package names.
     * @param ComposerRepository $repository   The repository.
     *
     * @return void
     */
    private function searchInPackageNames($packageNames, ComposerRepository $repository)
    {
        foreach ($packageNames as $packageName) {
            try {
                $packages = $repository->findPackages($packageName);
                $this->searchInPackages($packages);
            } catch (\Exception $e) {
                $this->application->renderException($e, $this->output);
            }
        }
    }

    /**
     * Search for dependencies in multiple packages.
     *
     * @param PackageInterface[] $packages The packages to search in.
     *
     * @return void
     */
    private function searchInPackages($packages)
    {
        foreach ($packages as $package) {
            $this->searchInPackage($package);
        }
    }

    /**
     * Search for dependencies in this package.
     *
     * @param PackageInterface $package The package to search in.
     *
     * @return void
     */
    private function searchInPackage(PackageInterface $package)
    {
        $this->progress->advance();

        if (
            $package instanceof AliasPackage
            || $this->searchInDevReleases && !$package->isDev()
            || $this->searchInReleases && $package->isDev()
        ) {
            return;
        }

        $this->progress->setMessage($package->getName());

        $requires = $package->getRequires();
        $this->searchInRequires($package, 'prod', $requires);

        $requires = $package->getDevRequires();
        $this->searchInRequires($package, 'dev', $requires);
    }

    /**
     * Search for dependencies in this package.
     *
     * @param PackageInterface $package  The package to search in.
     * @param string           $type     One of "prod" or "dev".
     * @param array|Link[]     $requires The require links.
     *
     * @return void
     */
    private function searchInRequires(PackageInterface $package, $type, array $requires)
    {
        if (isset($requires[$this->packageName])) {
            $link       = $requires[$this->packageName];
            $constraint = $link->getPrettyConstraint();

            $section = $package->getPrettyString();
            $message = sprintf(
                '<comment>%s</comment> %s %s',
                'dev' == $type ? 'require-dev' : 'require',
                $this->packageName,
                $constraint
            );

            $this->progress->clear();
            // Hack to get the cursor on the line beginning
            $this->output->write("\n\033[1A");
            $this->output->writeln($this->formatter->formatSection($section, $message));
            $this->progress->display();
        }
    }
}
