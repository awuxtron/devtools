<?php

namespace Awuxtron\Dev\Composer;

use Awuxtron\Dev\Composer\Commands\AnalyseCommand;
use Awuxtron\Dev\Composer\Commands\FormatCommand;
use Awuxtron\Dev\Composer\Commands\LintCommand;
use Composer\Command\BaseCommand;
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use Ramsey\Dev\Repl\Composer\ReplPlugin;

class ComposerPlugin implements Capable, CommandProvider, PluginInterface
{
    /**
     * ramsey/composer-repl plugin.
     *
     * @var ReplPlugin
     */
    private ReplPlugin $replPlugin;

    public function __construct()
    {
        $this->replPlugin = new ReplPlugin;
    }

    /**
     * {@inheritDoc}
     *
     * @return array<string, string>
     */
    public function getCapabilities(): array
    {
        return [
            CommandProvider::class => self::class,
        ];
    }

    /**
     * {@inheritDoc}
     *
     * @return BaseCommand[]
     */
    public function getCommands(): array
    {
        return array_merge($this->replPlugin->getCommands(), [
            new AnalyseCommand,
            new FormatCommand,
            new LintCommand,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->replPlugin->activate($composer, $io);
    }

    /**
     * {@inheritDoc}
     */
    public function deactivate(Composer $composer, IOInterface $io): void
    {
        $this->replPlugin->deactivate($composer, $io);
    }

    /**
     * {@inheritDoc}
     */
    public function uninstall(Composer $composer, IOInterface $io): void
    {
        $this->replPlugin->uninstall($composer, $io);
    }
}
