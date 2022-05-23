<?php

namespace Awuxtron\Dev\Composer;

use Awuxtron\Dev\Utils\ComposerPlugin;
use CaptainHook\App\Console\Application;
use Composer\Composer;
use Exception;
use Symfony\Component\Console\Input\ArgvInput;

class InstallCaptainHook
{
    private string $cache = 'vendor/captainhook.json';

    public function __construct(protected Composer $composer)
    {
        if ($this->isInstalled()) {
            return;
        }

        $configPath = ComposerPlugin::getCurrentPluginPath('captainhook.json');
        $conventionalPath = ComposerPlugin::getCurrentPluginPath('styles/conventional-commits.json');
        $conventional = json_decode((string) file_get_contents($conventionalPath), true, 512, JSON_THROW_ON_ERROR);
        $extra = ComposerPlugin::getExtra($composer, 'ramsey/conventional-commits');
        $config = [];

        if (!empty($c = file_get_contents($configPath))) {
            $config = json_decode($c, true, 512, JSON_THROW_ON_ERROR);
        }

        if (!empty($extra)) {
            $config['commit-msg']['actions'][0]['options'] = $extra;
        } else {
            $config['commit-msg']['actions'][0]['options']['config'] = $conventional;

            unset($config['commit-msg']['actions'][0]['options']['configFile']);
        }

        // Add test command if exists.
        if (!empty($composer->getPackage()->getScripts()['test'])) {
            $config['pre-commit']['actions'][] = [
                'action' => 'composer test',
                'options' => [],
                'conditions' => [],
            ];
        }

        $root = ComposerPlugin::getRootPath();

        if (file_exists("{$root}/.git")) {
            $p = "{$root}/{$this->cache}";

            try {
                file_put_contents($p, json_encode($config, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

                $args = ["{$root}/vendor/bin/captainhook", 'install', '-f', '-s', '-b', 'autoload.php', '-c', $p];

                $captainhook = new Application($args[0]);
                $captainhook->run(new ArgvInput($args));
            } catch (Exception $e) {
                unlink($p);

                throw $e;
            }
        }
    }

    protected function isInstalled(): bool
    {
        if (file_exists(ComposerPlugin::getRootPath('captainhook.json'))) {
            return true;
        }

        $extra = ComposerPlugin::getExtra($this->composer, 'captainhook');

        return $extra !== false && !empty($extra['config']);
    }
}
