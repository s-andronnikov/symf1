<?php
/**
 *
 */
namespace AppBundle\Service;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AbstractChainCommand
 */
abstract class AbstractChainCommand extends ContainerAwareCommand
{
    /** @var  string */
    private $parent;

    /** @var  int */
    private $priority;

    /** @var AbstractChainCommand[]  */
    private static $chainCommands;


    /**
     * constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->parent = null;
        $this->priority = 0;

        self::$chainCommands = is_array(self::$chainCommands) ? self::$chainCommands : array();
        array_push(self::$chainCommands, $this);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param bool            $direct
     *
     * @return int
     *
     * @throws \Exception
     */
    public function run(InputInterface $input, OutputInterface $output, $direct = false)
    {
        if (false === $direct && true === $this->hasParent()) {
            throw new \Exception('This a child command and can not be executed directly');
        }

        $result = parent::run($input, $output);

        // execute children;
        $children = $this->getChildren();
        if ($children) {
            foreach ($children as $child) {
                $child->run($input, $output, true);
            }
        }

        return $result;
    }

    /**
     * @param string $parent
     *
     * @return AbstractChainCommand
     */
    protected function setParent(string $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return string
     */
    protected function getParent(): ?string
    {
        return $this->parent;
    }

    /**
     * @return bool
     */
    protected function hasParent(): bool
    {
        return ($this->parent ? true : false);
    }

    /**
     * @return int
     */
    protected function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     */
    protected function setPriority(int $priority)
    {
        $this->priority = $priority;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
    }

    /**
     * @return AbstractChainCommand[]
     */
    private function getChildren(): array
    {
        $name = $this->getName();

        $children = array_filter(
            is_array(self::$chainCommands) ? self::$chainCommands : [],
            function (AbstractChainCommand $command) use ($name) {
                return $command->getParent() && $name === $command->getParent();
            }
        );

        usort(
            $children,
            function (AbstractChainCommand $command1, AbstractChainCommand $command2) {
                if ($command1->getPriority() === $command2->getPriority()) {
                    return 0;
                }

                return ($command1->getPriority() > $command2->getPriority()) ? -1 : 1;
            }
        );

        return $children;
    }
}
