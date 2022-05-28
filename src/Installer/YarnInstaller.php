<?php

namespace PantheonSalesEngineering\NodeComposer\Installer;

use Composer\IO\IOInterface;
use Composer\Util\RemoteFilesystem;
use InvalidArgumentException;
use PantheonSalesEngineering\NodeComposer\BinLinker;
use PantheonSalesEngineering\NodeComposer\NodeContext;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class YarnInstaller extends BaseInstaller
{
    /**
     * NodeDownloader constructor.
     * @param IOInterface $io
     * @param RemoteFilesystem $remoteFs
     * @param NodeContext $context
     * @param string $version
     * @param string $executable
     * @param string|null $downloadUriTemplate
     */
    public function __construct(
        IOInterface $io,
        RemoteFilesystem $remoteFs,
        NodeContext $context,
        string $version,
        string $executable,
        string $downloadUriTemplate = null
    ) {
        parent::__construct($io, $remoteFs, $context, $version, $executable, $downloadUriTemplate);
    }

    /**
     * @return bool
     * @throws InvalidArgumentException
     */
    public function install(): bool
    {
        $process = new Process(['npm install --global yarn@'. $this->version], $this->context->getBinDir());
        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                $this->io->writeError($buffer, true, IOInterface::DEBUG);
            } else {
                $this->io->write($buffer, true, IOInterface::DEBUG);
            }
        });

        // If process broke, throw error.
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        try {
            $sourceDir = $this->getNpmBinaryPath();
            $this->io->write('NPM found at: ' . $sourceDir, true, IOInterface::VERBOSE);
            $this->linkExecutables($sourceDir, $this->context->getBinDir());
            return true;
        } catch (ProcessFailedException $exception) {
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
     * @throws ProcessFailedException
     */
    private function getNpmBinaryPath(): string
    {
        $process = new Process(['npm', '-g bin'], $this->context->getBinDir());
        $process->run();

        // If process broke, throw error.
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // Return bin path
        $output = explode("\n", $process->getIncrementalOutput());
        return $output[0];
    }
}