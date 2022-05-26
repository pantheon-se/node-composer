<?php

namespace PantheonSalesEngineering\NodeComposer\Installer;

use Composer\IO\IOInterface;
use InvalidArgumentException;
use PantheonSalesEngineering\NodeComposer\BinLinker;
use PantheonSalesEngineering\NodeComposer\InstallerInterface;
use PantheonSalesEngineering\NodeComposer\NodeContext;
use Symfony\Component\Process\Process;

class YarnInstaller implements InstallerInterface
{
    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var NodeContext
     */
    private $context;

    /**
     * NodeDownloader constructor.
     * @param IOInterface $io
     * @param NodeContext $context
     */
    public function __construct(
        IOInterface $io,
        NodeContext $context
    ) {
        $this->io = $io;
        $this->context = $context;
    }

    /**
     * @param string $version
     * @return bool
     *@throws InvalidArgumentException
     */
    public function install(string $version): bool
    {

        $process = new Process(
            ['npm install --global yarn@'. $version],
            $this->context->getBinDir()
        );
        $process->setIdleTimeout(null);
        $process->setTimeout(null);
        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                $this->io->writeError($buffer, true, IOInterface::DEBUG);
            } else {
                $this->io->write($buffer, true, IOInterface::DEBUG);
            }
        });

        if (!$process->isSuccessful()) {
            throw new \RuntimeException('Could not install yarn');
        }

        $sourceDir = $this->getNpmBinaryPath();
        $this->io->write('NPM found at: ' . $sourceDir, true, IOInterface::VERBOSE);

        $this->linkExecutables($sourceDir, $this->context->getBinDir());

        return true;
    }

    public function isInstalled()
    {
        $process = new Process(["yarn --version"], $this->context->getBinDir());
        $process->setIdleTimeout(null);
        $process->setTimeout(null);
        $process->run();

        if ($process->isSuccessful()) {
            $output = explode("\n", $process->getIncrementalOutput());
            return $output[0];
        } else {
            return false;
        }
    }

    /**
     * @param string $sourceDir
     * @param string $targetDir
     */
    private function linkExecutables(string $sourceDir, string $targetDir)
    {
        $yarnPath = $this->context->getOsType() === 'win' ?
            realpath($sourceDir . DIRECTORY_SEPARATOR . 'yarn.cmd') :
            realpath($sourceDir . DIRECTORY_SEPARATOR . 'yarn');
        $yarnLink = $targetDir . DIRECTORY_SEPARATOR . 'yarn';

        $fs = new BinLinker(
            $this->context->getBinDir(),
            $this->context->getOsType()
        );
        $fs->unlinkBin($yarnLink);
        $fs->linkBin($yarnPath, $yarnLink);

        $yarnpkgPath = $this->context->getOsType() === 'win' ?
            realpath($sourceDir . DIRECTORY_SEPARATOR . 'yarnpkg.cmd') :
            realpath($sourceDir . DIRECTORY_SEPARATOR . 'yarnpkg');
        $yarnpkgLink = $targetDir . DIRECTORY_SEPARATOR . 'yarnpkg';

        $fs->unlinkBin($yarnpkgLink);
        $fs->linkBin($yarnpkgPath, $yarnpkgLink);
    }

    /**
     * @return string
     */
    private function getNpmBinaryPath(): string
    {
        $process = new Process(['npm -g bin'], $this->context->getBinDir());
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException('npm must be installed');
        } else {
            $output = explode("\n", $process->getIncrementalOutput());
            return $output[0];
        }
    }
}