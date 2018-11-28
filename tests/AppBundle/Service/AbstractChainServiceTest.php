<?php
/**
 *
 */
namespace Tests\AppBundle\Service;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class ChainServiceTestTest
 */
class ChainServiceTestTest extends KernelTestCase
{
    /** @var  Application */
    private $application;

    /**
     *
     */
    public function testParentOnly()
    {
        ChainServiceTest::cleanChainCommands();

        $parent = $this->createParentCommand();

        $application = $this->getApplication();
        $application->add($parent);

        $commandTester = new CommandTester($parent);
        $commandTester->execute(array(
            'command'  => $parent->getName(),
        ));

        $this->assertEquals('parent-command-output'.PHP_EOL, $commandTester->getDisplay());
    }

    /**
     *
     */
    public function testChainParentWithChild()
    {
        ChainServiceTest::cleanChainCommands();

        $application = $this->getApplication();

        $parent = $this->createParentCommand();
        $child = $this->createChildCommand('test:child', $parent->getName());

        $application->addCommands([
            $parent,
            $child,
        ]);

        $commandTester = new CommandTester($parent);
        $commandTester->execute(array(
            'command'  => $parent->getName(),
        ));

        $this->assertEquals('parent-command-output'.PHP_EOL.'test:child-command-output'.PHP_EOL, $commandTester->getDisplay());

        $child->setHidden(true);
    }

    /**
     *
     */
    public function testChainParentWithChildrenPriority()
    {
        ChainServiceTest::cleanChainCommands();

        $parent = $this->createParentCommand();
        $child1 = $this->createChildCommand('test:child1', $parent->getName(), 10);
        $child2 = $this->createChildCommand('test:child2', $parent->getName(), 20);

        $application = $this->getApplication();

        $application->addCommands([
            $parent,
            $child1,
            $child2,
        ]);

        $commandTester = new CommandTester($parent);
        $commandTester->execute(array(
            'command'  => $parent->getName(),
        ));

        $this->assertEquals('parent-command-output'.PHP_EOL.'test:child2-command-output'.PHP_EOL.'test:child1-command-output'.PHP_EOL, $commandTester->getDisplay());
    }

    /**
     * @return ChainServiceTest
     */
    private function createParentCommand()
    {
        $command = new ChainServiceTest();
        $command->setName('test:parent')->setCode(function (InputInterface $input, OutputInterface $output) {
            $output->writeln('parent-command-output');
        });

        return $command;
    }

    /**
     * @param string $name
     * @param string $parent
     * @param int    $priority
     *
     * @return ChainServiceTest
     */
    private function createChildCommand(string $name = 'test:child', string $parent = 'test:parent', int $priority = 0)
    {
        $command = new ChainServiceTest($parent, $priority);
        $command
            ->setName($name)
            ->setCode(function (InputInterface $input, OutputInterface $output) use ($command) {
                $output->writeln(sprintf('%s-command-output', $command->getName()));
            });

        return $command;
    }

    /**
     * @return Application
     */
    private function getApplication()
    {
        if (!$this->application) {
            $this->application = new Application(static::createKernel());
        }

        return $this->application;
    }
}
