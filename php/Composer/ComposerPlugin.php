<?php

namespace Awuxtron\Dev\Composer;

use Awuxtron\Dev\Composer\Commands\AnalyseCommand;
use Awuxtron\Dev\Composer\Commands\FormatCommand;
use Awuxtron\Dev\Composer\Commands\LintCommand;
use Composer\Command\BaseCommand;
use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Composer\Util\Filesystem;
use Ramsey\Dev\Repl\Composer\ReplPlugin;
use ZipArchive;

class ComposerPlugin implements Capable, CommandProvider, PluginInterface, EventSubscriberInterface
{
    /**
     * ramsey/composer-repl plugin.
     *
     * @var ReplPlugin
     */
    private ReplPlugin $replPlugin;

    /**
     * @var array<string, string>
     */
    private array $unrequiredPackages = [
        'phpstan/phpstan-strict-rules:*' => 'analysis/extensions/phpstan-strict-rules',
    ];

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
        $this->downloadUnrequiredPackage($composer);
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

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => 'process',
            ScriptEvents::POST_UPDATE_CMD => 'process',
        ];
    }

    public function process(Event $event): void
    {
        $composer = $event->getComposer();

        new InstallCaptainHook($composer);

        $localRepo = $composer->getRepositoryManager()->getLocalRepository();
        $pssr = $localRepo->findPackage('phpstan/phpstan-strict-rules', '*');
        $neon = explode(PHP_EOL, (string) file_get_contents($pn = __DIR__ . '/../../analysis/phpstan.neon'));
        $str = '    - extensions/strict-rules.neon';

        if ($pssr !== null) {
            unset($neon[1]);
        } elseif ($neon[1] != $str) {
            array_splice($neon, 1, 0, $str);
        }

        file_put_contents($pn, implode(PHP_EOL, $neon));
    }

    protected function downloadUnrequiredPackage(Composer $composer): void
    {
        $root = __DIR__ . '/../..';
        $downloader = $composer->getDownloadManager()->getDownloader('zip');

        foreach ($this->unrequiredPackages as $name => $target) {
            if (file_exists($to = "{$root}/{$target}")) {
                continue;
            }

            $package = $composer->getRepositoryManager()->findPackage(...explode(':', $name, 2));

            if ($package !== null) {
                $name = str_replace('/', '-', $package->getName());

                $downloader->download($package, $to)->then(function ($p) use ($to, $name) {
                    $zip = new ZipArchive;
                    $res = $zip->open($p);

                    if ($res === true) {
                        $zip->extractTo($to);
                        $zip->close();
                    }

                    $extracted = glob("{$to}/{$name}-*", GLOB_ONLYDIR);

                    if (!empty($extracted)) {
                        foreach ($extracted as $sub) {
                            (new Filesystem)->rename($sub, $to);
                        }
                    }
                });
            }
        }
    }
}
