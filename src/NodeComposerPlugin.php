<?php

namespace PantheonSalesEngineering\NodeComposer;


use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Composer\Util\RemoteFilesystem;
use Exception;
use PantheonSalesEngineering\NodeComposer\Exception\VersionVerificationException;
use PantheonSalesEngineering\NodeComposer\Exception\NodeComposerConfigException;
use PantheonSalesEngineering\NodeComposer\Installer\NodeInstaller;
use PantheonSalesEngineering\NodeComposer\Installer\YarnInstaller;
use Symfony\Component\Process\Exception\ProcessFailedException;

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
            throw new NodeComposerConfigException(sprintf('You must configure the Node Composer plugin. See setup instructions at: %s', 'https://github.com/pantheon-se/node-composer'));
        }

        $this->config = Config::fromArray($extraConfig['pantheon-se']['node-composer']);
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
     * @throws Exception
     */
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
            $this->config->getNodeVersion(),
            'node',
            $this->config->getNodeDownloadUrl()
        );
        $nodeInstaller->init();

        // Validate Yarn
        if ($this->config->getYarnVersion() !== null) {
            $yarnInstaller = new YarnInstaller(
                $this->io,
                new RemoteFilesystem($this->io, $this->composer->getConfig()),
                $context,
                $this->config->getYarnVersion(),
                'yarn'
            );
            $yarnInstaller->init();

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
