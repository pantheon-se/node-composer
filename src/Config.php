<?php

namespace PantheonSalesEngineering\NodeComposer;

use PantheonSalesEngineering\NodeComposer\Exception\NodeComposerConfigException;

class Config
{
    /**
     * Version of Node.js.
     * @var string
     */
    private $nodeVersion;

    /**
     * Version of yarn.
     * @var string
     */
    private $yarnVersion;

    /**
     * Template string for downloading Node.js versions.
     * @var string
     */
    private $nodeDownloadUrl;

    /**
     * URL to resolve node versions.
     * @var string
     */
    private $nodeVersionUrl = "https://resolve-node.vercel.app";

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

        $self->nodeVersion = $conf['node-version'] ?? null;
        $self->nodeDownloadUrl = $conf['node-download-url'] ?? null;
        $self->yarnVersion = $conf['yarn-version'] ?? null;

        if ($self->getNodeVersion() === null) {
            throw new NodeComposerConfigException('You must specify a node-version');
        }

        return $self;
    }

    /**
     * @return string
     */
    public function getNodeVersion(): string
    {
        if (is_string($this->nodeVersion)) {
            return $this->nodeVersion;
        }

        return $this->getNodeLatestLTS();
    }

    /**
     * @return string
     */
    public function getYarnVersion(): ?string
    {

        if (is_bool($this->yarnVersion) && $this->yarnVersion) {
            $this->yarnVersion = 'latest';
        }

        return $this->yarnVersion;
    }

    /**
     * @return string|null
     */
    public function getNodeDownloadUrl(): ?string
    {
        return $this->nodeDownloadUrl;
    }

    public function getNodeLatestLTS(): string
    {
        $api_url = $this->nodeVersionUrl . '/lts';
        $this->nodeVersion = ltrim(file_get_contents($api_url), "v");
        return $this->nodeVersion;
    }
}