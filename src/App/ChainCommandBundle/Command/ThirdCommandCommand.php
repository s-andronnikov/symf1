<?php
/**
 *
 */
namespace App\ChainCommandBundle\Command;

use AppBundle\Service\AbstractChainCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ThirdCommandCommand
 */
class ThirdCommandCommand extends AbstractChainCommand
{
    /**
     * AnotherCommandCommand constructor.
     */
    public function __construct()
    {
        parent::__construct('foo:hello', 200);
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('foo:third')
            ->setDescription('...')
            ->addArgument('argument', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Command third.');
    }
}
