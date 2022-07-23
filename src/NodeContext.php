<?php

namespace PantheonSalesEngineering\NodeComposer;


class NodeContext
{
    /**
     * @var string
     */
    private $vendorDir;

    /**
     * @var string
     */
    private $binDir;

    /**
     * @var string
     */
    private $osType;

    /**
     * @var string
     */
    private $systemArchitecture;

    /**
     * NodeContext constructor.
     * @param string $vendorDir
     * @param string $binDir
     * @param string|null $osType
     * @param string|null $systemArchitecture
     */
    public function __construct(
        string $vendorDir,
        string $binDir,
        string $osType = null,
        string $systemArchitecture = null
    ) {
        $this->vendorDir = $vendorDir;
        $this->binDir = $binDir;

        $this->osType = $osType;
        if (!$this->osType) {
            $this->osType = stripos(PHP_OS, 'WIN') === 0 ? 'win' : strtolower(PHP_OS);
        }

        $this->systemArchitecture = is_string($systemArchitecture) ? $systemArchitecture : php_uname('m');

        // Arch distributions use arm64 node packages.
        // https://github.com/nodejs/nodejs.org/issues/2661
        if ($this->systemArchitecture == 'aarch64') {
            $this->systemArchitecture = 'arm64';
        }
    }

    /**
     * @return string
     */
    public function getOsType(): ?string
    {
        return $this->osType;
    }

    /**
     * @return string
     */
    public function getSystemArchitecture(): string
    {
        return $this->systemArchitecture;
    }

    /**
     * @return string
     */
    public function getVendorDir(): string
    {
        return $this->vendorDir;
    }

    /**
     * @return string
     */
    public function getBinDir(): string
    {
        return $this->binDir;
    }
}