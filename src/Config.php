<?php

namespace PantheonSalesEngineering\NodeComposer;

use PantheonSalesEngineering\NodeComposer\Exception\NodeComposerConfigException;

class Config
{
    /**
     * @var string
     */
    private $nodeVersion;

    /**
     * @var string
     */
    private $yarnVersion;

    /**
     * @var string
     */
    private $nodeDownloadUrl;

    /**
     * Config constructor.
     */
    private function __construct()
    {
    }

    /**
     * @param array $conf
     * @return Config
     */
    public static function fromArray(array $conf): Config
    {
        $self = new self();

        $self->nodeVersion = $conf['node-version'];
        $self->nodeDownloadUrl = $conf['node-download-url'] ?? null;
        $self->yarnVersion = $conf['yarn-version'] ?? null;

        if ($self->nodeVersion === null) {
            throw new NodeComposerConfigException('You must specify a node-version');
        }


        return $self;
    }

    /**
     * @return string
     */
    public function getNodeVersion(): string
    {
        return $this->nodeVersion;
    }

    /**
     * @return string
     */
    public function getYarnVersion(): string
    {
        return $this->yarnVersion;
    }

    /**
     * @return string|null
     */
    public function getNodeDownloadUrl(): ?string
    {
        return $this->nodeDownloadUrl;
    }
}