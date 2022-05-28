<?php

namespace PantheonSalesEngineering\NodeComposer\Installer;

use Composer\IO\IOInterface;
use Composer\Util\RemoteFilesystem;
use PantheonSalesEngineering\NodeComposer\NodeContext;


class NodeInstaller extends BaseInstaller
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
}