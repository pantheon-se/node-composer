<?php

namespace PantheonSalesEngineering\NodeComposer;


interface InstallerInterface
{
    /**
     * @return bool
     */
    public function install(): bool;

    /**
     * @return bool
     */
    public function isInstalled(): bool;

    /**
     * @param string $version
     * @return bool|string
     */
    public function versionMatches(string $version);
}