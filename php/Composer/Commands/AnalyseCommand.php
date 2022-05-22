<?php

namespace Awuxtron\Dev\Composer\Commands;

use Composer\Command\BaseCommand;
use Composer\Factory;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AnalyseCommand extends BaseCommand
{
    /**
     * Default paths pass to phpstan.
     *
     * @var string[]
     */
    private array $paths = ['app', 'src'];

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setName('analyse');
        $this->setDescription('Analyses source code using PHPStan.');

        $this->addArgument(
            'args',
            InputArgument::IS_ARRAY,
            'Arguments to pass to PHPStan analyse command.'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $workingDir = realpath(dirname(Factory::getComposerFile()));

        if (!file_exists($baseline = "{$workingDir}/phpstan-baseline.neon")) {
            file_put_contents($baseline, '');
        }

        $paths = [];
        $options = [];
        $config = !file_exists("{$workingDir}/phpstan.neon") && !file_exists("{$workingDir}/phpstan.neon.dist");

        foreach ($input->getArgument('args') as $arg) {
            if (!str_starts_with($arg, '-') && !str_starts_with($arg, '--')) {
                $paths[] = $arg;

                continue;
            }

            if (str_starts_with($arg, '-c') || str_starts_with($arg, '--configuration')) {
                $config = false;
            }

            $options[] = $arg;
        }

        if (empty($paths)) {
            $paths = array_filter(array_map(fn ($p) => "{$workingDir}/{$p}", $this->paths), 'file_exists');
        }

        $ext = '\\PHPStan\\ExtensionInstaller\\GeneratedConfig';

        if (class_exists($ext) && isset($ext::EXTENSIONS['awuxtron/devtools'])) {
            $config = false;
        }

        if ($config) {
            $options[] = "--configuration={$workingDir}/vendor/awuxtron/devtools/analysis/phpstan.neon";
        }

        $execInputs = new ArrayInput([
            'binary' => 'phpstan',
            'args' => array_merge(['analyse', '--level=8'], array_values($paths), $options),
        ]);

        return $this->getApplication()->find('exec')->run($execInputs, $output);
    }
}
