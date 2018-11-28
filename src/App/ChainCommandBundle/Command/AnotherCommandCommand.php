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
 * Class AnotherCommandCommand
 */
class AnotherCommandCommand extends AbstractChainCommand
{
    /**
     * AnotherCommandCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->setParent('foo:hello');
        $this->setPriority(100);
    }

    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('bar:hi')
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
        $output->writeln('Hi from Bar!');
    }
}
