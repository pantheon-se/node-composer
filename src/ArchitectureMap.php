<?php

namespace PantheonSalesEngineering\NodeComposer;

class ArchitectureMap
{
    /**
     * @var array
     */
    private static $map = array(
        'x86_64' => 'x64',
        'amd64' => 'x64',
        'i586' => 'x86',
    );

    /**
     * @param string $phpArchitecture
     * @return string
     */
    public static function getNodeArchitecture(string $phpArchitecture): string
    {
        $lowercaseArchitecture = strtolower($phpArchitecture);
        return static::$map[$lowercaseArchitecture] ?? $phpArchitecture;
    }
}