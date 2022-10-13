<?php

namespace PantheonSalesEngineering\NodeComposer\Installer;

use Composer\IO\IOInterface;
use Composer\Util\RemoteFilesystem;
use PantheonSalesEngineering\NodeComposer\Installer;
use PantheonSalesEngineering\NodeComposer\NodeContext;

class NodeInstaller extends Installer
{

    /**
     * NodeDownloader constructor.
     * @param IOInterface $io
     * @param RemoteFilesystem $remoteFs
     * @param NodeContext $context
     * @param string|null $downloadUriTemplate
     */
    public function __construct(
        IOInterface $io,
        RemoteFilesystem $remoteFs,
        NodeContext $context,
        string $downloadUriTemplate = null
    ) {
        $this->io->write("Function: " . __FUNCTION__ . " Line: " . __LINE__);
        // Declare download template.
        $downloadUriTemplate = (!empty($downloadUriTemplate)) ? $downloadUriTemplate : 'https://nodejs.org/dist/v${version}/node-v${version}-${osType}-${architecture}.${format}';

        // Setup paths for executables.
        $executableList = [
            'node' => [
                'nix' => 'bin/node',
                'link' => 'node',
                'win' => 'node.exe',
            ],
            'npm' => [
                'nix' => 'bin/npm',
                'link' => 'npm',
                'win' => 'npm.cmd',
            ]
        ];

        // Declare command to check if installed.
        $installedCommand = ["node", "--version"];
        $this->io->write("Function: " . __FUNCTION__ . " Line: " . __LINE__);
        // Initialize object.
        parent::__construct($io, $remoteFs, $context, $downloadUriTemplate, $installedCommand, $executableList);
        $this->io->write("Function: " . __FUNCTION__ . " Line: " . __LINE__);
    }
}