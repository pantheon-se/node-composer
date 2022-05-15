<?php

namespace PantheonSalesEngineering\NodeComposer;


interface InstallerInterface
{
    /**
     * @param string $version
     * @return bool
     */
    public function install(string $version): bool;

    /**
     * @return string|false
     */
    public function isInstalled();
}