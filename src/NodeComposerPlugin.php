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
use PantheonSalesEngineering\NodeComposer\Exception\NodeComposerConfigException;
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

        if (!isset($extraConfig['pantheon-se']['node-composer'])) {
            throw new NodeComposerConfigException('You must configure the node composer plugin');
        }

        $this->config = Config::fromArray($extraConfig['pantheon-se']['node-composer']);
    }

    /**
     * @return \array[][]
     */
    public static function getSubscribedEvents(): array
    {
        return array(
            ScriptEvents::POST_UPDATE_CMD => array(
                array('onPostUpdate', 1)
            ),
            ScriptEvents::POST_INSTALL_CMD => array(
                array('onPostUpdate', 1)
            )
        );
    }

    public function onPostUpdate(Event $event)
    {
        $context = new NodeContext(
            $this->composer->getConfig()->get('vendor-dir'),
            $this->composer->getConfig()->get('bin-dir')
        );

        $nodeInstaller = new NodeInstaller(
            $this->io,
            new RemoteFilesystem($this->io, $this->composer->getConfig()),
            $context,
            $this->config->getNodeDownloadUrl()
        );

        $installedNodeVersion = $nodeInstaller->isInstalled();

        if (
            $installedNodeVersion === false ||
            strpos($installedNodeVersion, 'v' . $this->config->getNodeVersion()) === false
        ) {
            $this->io->write(sprintf(
                'Installing node.js v%s',
                $this->config->getNodeVersion()
            ));

            $nodeInstaller->install($this->config->getNodeVersion());

            $installedNodeVersion = $nodeInstaller->isInstalled();
            if (strpos($installedNodeVersion, 'v' . $this->config->getNodeVersion()) === false) {
                $this->io->write(array_merge(['Bin files:'], glob($context->getBinDir() . '/*.*')), true, IOInterface::VERBOSE);
                throw new VersionVerificationException('nodejs', $this->config->getNodeVersion(), $installedNodeVersion);
            } else {
                $this->io->overwrite(sprintf(
                    'node.js v%s installed',
                    $this->config->getNodeVersion()
                ));
            }
        }

        if ($this->config->getYarnVersion() !== null) {
            $yarnInstaller = new YarnInstaller(
                $this->io,
                $context
            );

            $installedYarnVersion = $yarnInstaller->isInstalled();

            if (
                $installedYarnVersion === false ||
                strpos($installedYarnVersion, $this->config->getYarnVersion()) === false
            ) {
                $this->io->write(sprintf(
                    'Installing yarn v%s',
                    $this->config->getYarnVersion()
                ));

                $yarnInstaller->install($this->config->getYarnVersion());

                $installedYarnVersion = $yarnInstaller->isInstalled();
                if (strpos($installedYarnVersion, $this->config->getYarnVersion()) === false) {
                    $this->io->write(array_merge(['Bin files:'], glob($context->getBinDir() . '/*.*')), true, IOInterface::VERBOSE);
                    throw new VersionVerificationException('yarn', $this->config->getYarnVersion(), $installedYarnVersion);
                } else {
                    $this->io->write(sprintf(
                        'node.js v%s installed',
                        $this->config->getNodeVersion()
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
