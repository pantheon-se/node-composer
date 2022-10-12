<?php

namespace PantheonSalesEngineering\NodeComposer;


use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Composer\Util\RemoteFilesystem;
use PantheonSalesEngineering\NodeComposer\Exception\VersionVerificationException;
use PantheonSalesEngineering\NodeComposer\Installer\NodeInstaller;
use PantheonSalesEngineering\NodeComposer\Installer\YarnInstaller;

class NodeComposerPlugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * @var Composer
     */
    private $composer;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var Config
     */
    private $config;

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;

        $extraConfig = $this->composer->getPackage()->getExtra();

        if (isset($extraConfig['pantheon-se']['node-composer'])) {
            $this->config = Config::fromArray($extraConfig['pantheon-se']['node-composer']);
        } else {
            $this->config = Config::fromNull();
        }
    }

    /**
     * @return \array[][]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::POST_UPDATE_CMD => [
                ['onPostUpdate', 1]
            ],
            ScriptEvents::POST_INSTALL_CMD => [
                ['onPostUpdate', 1]
            ]
        ];
    }

    /**
     * @throws \Exception
     */
    public function onPostUpdate(Event $event)
    {
        $fs = new RemoteFilesystem($this->io, $this->composer->getConfig());
        $context = new NodeContext(
            $this->composer->getConfig()->get('vendor-dir'),
            $this->composer->getConfig()->get('bin-dir')
        );

        $nodeInstaller = new NodeInstaller(
            $this->io,
            $fs,
            $context,
            $this->config->getNodeDownloadUrl()
        );

        $installedNodeVersion = $nodeInstaller->isInstalled();
        $nodeVersion = 'v' . $this->config->getNodeVersion();

        if (
            $installedNodeVersion === false ||
            strpos($installedNodeVersion, $nodeVersion) === false
        ) {
            $this->io->write(sprintf(
                'Installing Node.js (%s)',
                $this->config->getNodeVersion()
            ));

            $nodeInstaller->install($this->config->getNodeVersion());

            $installedNodeVersion = $nodeInstaller->isInstalled();
            if (strpos($installedNodeVersion, $nodeVersion) === false) {
                $this->io->write(array_merge(['Bin files:'], glob($context->getBinDir() . '/*.*')), true, IOInterface::VERBOSE);
                throw new VersionVerificationException('Node.js', $nodeVersion, $installedNodeVersion);
            } else {
                $this->io->overwrite(sprintf(
                    'Node.js installed (%s)',
                    $this->config->getNodeVersion()
                ));
            }
        }

        // Validate Yarn
        if ($this->config->getYarnVersion() !== null) {

            $yarnInstaller = new YarnInstaller(
                $this->io,
                $fs,
                $context
            );

            $installedYarnVersion = $yarnInstaller->isInstalled();

            if (
                $installedYarnVersion === false ||
                strpos($installedYarnVersion, $this->config->getYarnVersion()) === false
            ) {
                // Installing Yarn
                $this->io->write(sprintf('Installing Yarn (%s)', $this->config->getYarnVersion()));

                $yarnInstaller->install($this->config->getYarnVersion());

                $installedYarnVersion = $yarnInstaller->isInstalled();
                if (strpos($installedYarnVersion, $this->config->getYarnVersion()) === false && $this->config->getYarnVersion() !== 'latest') {
                    $this->io->write(array_merge(['Bin files:'], glob($context->getBinDir() . '/*.*')), true, IOInterface::VERBOSE);
                    throw new VersionVerificationException('yarn', $this->config->getYarnVersion(), $installedYarnVersion);
                } else {
                    $this->io->write(sprintf(
                        'Yarn installed (%s)',
                        $this->config->getYarnVersion()
                    ));
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function deactivate(Composer $composer, IOInterface $io)
    {
    }

    /**
     * @inheritDoc
     */
    public function uninstall(Composer $composer, IOInterface $io)
    {
    }
}
