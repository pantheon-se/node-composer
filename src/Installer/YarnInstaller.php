<?php

namespace PantheonSalesEngineering\NodeComposer\Installer;

use Composer\IO\IOInterface;
use Composer\Util\RemoteFilesystem;
use Exception;
use InvalidArgumentException;
use PantheonSalesEngineering\NodeComposer\Installer;
use PantheonSalesEngineering\NodeComposer\NodeContext;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class YarnInstaller extends Installer
{
    /**
     * NodeDownloader constructor.
     * @param IOInterface $io
     * @param RemoteFilesystem $remoteFs
     * @param NodeContext $context
     */
    public function __construct(
        IOInterface $io,
        RemoteFilesystem $remoteFs,
        NodeContext $context
    ) {

        // Declare command to check if installed.
        $installedCommand = ["yarn", "--version"];

        // Setup paths for executables.
        $executableList = [
            'yarn' => [
                'link' => 'yarn',
                'nix' => '.bin/yarn',
                'win' => '.bin\yarn.cmd',
            ],
            'yarnpkg' => [
                'link' => 'yarnpkg',
                'nix' => '.bin/yarnpkg',
                'win' => '.bin\yarnpkg.cmd',
            ]
        ];

        // Empty template for yarn.
        $downloadUriTemplate = "";

        // Initialize object.
        parent::__construct($io, $remoteFs, $context, $downloadUriTemplate, $installedCommand, $executableList);
    }

    /**
     * @param string $version
     * @return bool
     * @throws InvalidArgumentException|Exception
     */
    public function install(string $version): bool
    {
        $sourceDir = $this->getNpmBinaryPath();
        $this->io->write('npm found at: ' . $sourceDir, true, IOInterface::VERBOSE);

        $process = new Process(
            ['npm', 'install', 'yarn@'. $version],
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
            throw new RuntimeException(sprintf('Could not install yarn: %s', $process->getErrorOutput()));
        }

        $sourceDir = $sourceDir . DIRECTORY_SEPARATOR . 'node_modules';
        $this->linkExecutables($sourceDir, $this->context->getBinDir());
        return true;
    }

    /**
     * Get path to npm binary directory.
     * @return string
     */
    private function getNpmBinaryPath(): string
    {
        $process = new Process(['npm', 'prefix'], $this->context->getBinDir());

        try {
            $process->run();
            $output = explode("\n", $process->getIncrementalOutput());
            return $output[0];
        } catch (ProcessFailedException $exception) {
            throw new RuntimeException(sprintf('npm must be installed: %s', $exception->getMessage()));
        }
    }
}