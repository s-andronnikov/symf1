<?php
/**
 *
 */
namespace App\ChainCommandBundle\Command;

use AppBundle\Service\AbstractChainService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AnotherCommandService
 */
class AnotherCommandCommand extends AbstractChainService
{
    /**
     * AnotherCommandService constructor.
     */
    public function __construct()
    {
        parent::__construct('foo:hello', 100);
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
