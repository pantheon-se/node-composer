<?php

namespace PantheonSalesEngineering\NodeComposer\Installer;

use Composer\IO\IOInterface;
use Composer\Util\RemoteFilesystem;
use InvalidArgumentException;
use PantheonSalesEngineering\NodeComposer\ArchitectureMap;
use PantheonSalesEngineering\NodeComposer\BinLinker;
use PantheonSalesEngineering\NodeComposer\InstallerInterface;
use PantheonSalesEngineering\NodeComposer\NodeContext;
use Symfony\Component\Process\Process;

class NodeInstaller implements InstallerInterface
{
    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var RemoteFilesystem
     */
    private $remoteFs;

    /**
     * @var NodeContext
     */
    private $context;

    /**
     * @var string
     */
    private $downloadUriTemplate;

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
        $this->io = $io;
        $this->remoteFs = $remoteFs;
        $this->context = $context;

        $this->downloadUriTemplate = is_string($downloadUriTemplate) ? $downloadUriTemplate :
            'https://nodejs.org/dist/v${version}/node-v${version}-${osType}-${architecture}.${format}';
    }

    /**
     * @param string $version
     * @return bool
     *@throws InvalidArgumentException
     */
    public function install(string $version): bool
    {

        $this->downloadExecutable($version);

        return true;
    }

    /**
     * @return string|false
     */
    public function isInstalled()
    {

        $process = new Process(["node --version"], $this->context->getBinDir());
        $process->run();

        if ($process->isSuccessful()) {
            $output = explode("\n", $process->getIncrementalOutput());
            return $output[0];
        } else {
            return false;
        }
    }

    /**
     * @param string $version
     */
    private function downloadExecutable(string $version)
    {
        $downloadUri = $this->buildDownloadLink($version);

        $fileName = $this->context->getVendorDir() . DIRECTORY_SEPARATOR .
            pathinfo(parse_url($downloadUri, PHP_URL_PATH), PATHINFO_BASENAME);

        $this->remoteFs->copy(
            parse_url($downloadUri, PHP_URL_HOST),
            $downloadUri,
            $fileName,
            true
        );

        $targetPath = $this->context->getVendorDir() . DIRECTORY_SEPARATOR .
            pathinfo(parse_url($downloadUri, PHP_URL_PATH), PATHINFO_BASENAME);

        $targetPath = preg_replace('/\.(tar\.gz|zip)$/', '', $targetPath);

        $this->unpackExecutable($fileName, $targetPath);

        $realNodeInstalledPath = is_dir($targetPath . DIRECTORY_SEPARATOR . basename($targetPath)) ?
            $targetPath . DIRECTORY_SEPARATOR . basename($targetPath) :
            $targetPath;
        $this->linkExecutables($realNodeInstalledPath, $this->context->getBinDir());
    }

    /**
     * @param string $version
     * @return string
     */
    private function buildDownloadLink(string $version): string
    {
        return preg_replace(
            array(
                '/\$\{version\}/',
                '/\$\{osType\}/',
                '/\$\{architecture\}/',
                '/\$\{format\}/'
            ),
            array(
                $version,
                strtolower($this->context->getOsType()),
                ArchitectureMap::getNodeArchitecture($this->context->getSystemArchitecture()),
                $this->context->getOsType() === 'win' ? 'zip' : 'tar.gz'
            ),
            $this->downloadUriTemplate
        );
    }

    /**
     * @param string $source
     * @param string $targetDir
     */
    private function unpackExecutable(string $source, string $targetDir)
    {
        if (realpath($targetDir)) {
            $files = glob($targetDir . DIRECTORY_SEPARATOR . '**' . DIRECTORY_SEPARATOR . '*');
            foreach ($files as $file) {
                unlink($file);
            }
        } else {
            mkdir($targetDir);
        }

        if (preg_match('/\.zip$/', $source) === 1) {
            $this->unzip($source, $targetDir);
        } else {
            $this->untar($source, $targetDir);
        }
    }

    /**
     * @param string $source
     * @param string $targetDir
     */
    private function unzip(string $source, string $targetDir)
    {
        $zip = new \ZipArchive();
        $res = $zip->open($source);
        if ($res === true) {
            // extract it to the path we determined above
            $zip->extractTo($targetDir);
            $zip->close();
        } else {
            throw new \RuntimeException(sprintf('Unable to extract file %s', $source));
        }

        unlink($source);
    }

    /**
     * @param string $source
     * @param string $targetDir
     */
    private function untar(string $source, string $targetDir)
    {
        $process = new Process(
            ["tar", "-xvf ".$source." -C ".escapeshellarg($targetDir)." --strip 1"]
        );
        $process->run();

        if (!$process->isSuccessful()) {
            $error = $process->getErrorOutput();
            throw new \RuntimeException(sprintf(
                'An error occurred while extracting NodeJS (%s) to %s. Error: %s',
                $source,
                $targetDir,
                $error
            ));
        }

        unlink($source);
    }

    /**
     * @param string $sourceDir
     * @param string $targetDir
     */
    private function linkExecutables(string $sourceDir, string $targetDir)
    {
        $nodePath = $this->context->getOsType() === 'win' ?
            realpath($sourceDir . DIRECTORY_SEPARATOR . 'node.exe') :
            realpath($sourceDir . DIRECTORY_SEPARATOR . 'bin/node');
        $nodeLink = $targetDir . DIRECTORY_SEPARATOR . 'node';

        $fs = new BinLinker(
            $this->context->getBinDir(),
            $this->context->getOsType()
        );
        $fs->unlinkBin($nodeLink);
        $fs->linkBin($nodePath, $nodeLink);

        $npmPath = $this->context->getOsType() === 'win' ?
            realpath($sourceDir . DIRECTORY_SEPARATOR . 'npm.cmd') :
            realpath($sourceDir . DIRECTORY_SEPARATOR . 'bin/npm');
        $npmLink = $targetDir . DIRECTORY_SEPARATOR . 'npm';

        $fs->unlinkBin($npmLink);
        $fs->linkBin($npmPath, $npmLink);
    }
}