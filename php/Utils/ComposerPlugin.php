<?php

namespace Awuxtron\Dev\Utils;

use Composer\Composer;
use Composer\Factory;

class ComposerPlugin
{
    /**
     * @param Composer    $composer
     * @param null|string $key
     *
     * @return array<mixed>|false
     */
    public static function getExtra(Composer $composer, ?string $key = null): array|bool
    {
        $extra = $composer->getPackage()->getExtra();

        if (empty($key)) {
            return $extra;
        }

        if (!empty($extra[$key])) {
            return $extra[$key];
        }

        return false;
    }

    public static function getRootPath(string $path = ''): string
    {
        $dir = dirname(Factory::getComposerFile());

        return (string) realpath(rtrim("{$dir}/{$path}", '/\\'));
    }

    public static function getCurrentPluginPath(string $path = ''): string
    {
        return rtrim(__DIR__ . "/../../{$path}", '/\\');
    }
}
