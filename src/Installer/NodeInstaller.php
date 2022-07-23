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
     * @param string $downloadUriTemplate
     */
    public function __construct(
        IOInterface $io,
        RemoteFilesystem $remoteFs,
        NodeContext $context,
        string $downloadUriTemplate
    ) {
        // Declare command to check if installed.
        $installedCommand = ["node", "--version"];

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

        // Initialize object.
        parent::__construct($io, $remoteFs, $context, $downloadUriTemplate, $installedCommand, $executableList);
    }
}