<?php

namespace Awuxtron\Dev\Composer\Commands;

use Composer\Command\BaseCommand;
use Composer\Factory;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FormatCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setName('format');
        $this->setDescription('Format source code using PHP CS Fixer.');

        $this->addArgument(
            'args',
            InputArgument::IS_ARRAY,
            'Arguments to pass to PHP CS Fixer.'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $workingDir = realpath(dirname(Factory::getComposerFile()));

        $paths = [];
        $options = [];

        $config = !file_exists("{$workingDir}/.php-cs-fixer.php") && !file_exists(
            "{$workingDir}/.php-cs-fixer.dist.php"
        );

        foreach ($input->getArgument('args') as $arg) {
            if (!str_starts_with($arg, '-') && !str_starts_with($arg, '--')) {
                $paths[] = $arg;

                continue;
            }

            if (str_starts_with($arg, '--config')) {
                $config = false;
            }

            $options[] = $arg;
        }

        if ($config) {
            $options[] = "--config={$workingDir}/vendor/awuxtron/devtools/styles/php_cs.php";
        }

        $execInputs = new ArrayInput([
            'binary' => 'php-cs-fixer',
            'args' => array_merge(['fix', '-v', '--allow-risky=yes'], array_values($paths), $options),
        ]);

        return $this->getApplication()->find('exec')->run($execInputs, $output);
    }
}
