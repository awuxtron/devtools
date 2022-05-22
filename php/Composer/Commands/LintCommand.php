<?php

namespace Awuxtron\Dev\Composer\Commands;

use Composer\Command\BaseCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LintCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setName('lint');
        $this->setDescription('Check your code style.');

        $this->addArgument(
            'args',
            InputArgument::IS_ARRAY,
            'Arguments to pass to lint tool.'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputs = new ArrayInput([
            'args' => ['--dry-run'],
        ]);

        return $this->getApplication()->find('format')->run($inputs, $output);
    }
}
