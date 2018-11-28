<?php
/**
 *
 */
namespace AppBundle\Service;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AbstractChainCommand
 */
class AbstractChainCommand extends ContainerAwareCommand
{
    /** @var  string */
    private $parent;

    /** @var  int */
    private $priority;

    /** @var AbstractChainCommand[]  */
    private static $chainCommands;

    /** @var  Logger|null */
    private static $logger;

    /**
     * AbstractChainCommand constructor.
     *
     * @param null|string $parent
     * @param int|null    $priority
     */
    public function __construct(?string $parent = null, ?int $priority = null)
    {
        parent::__construct();

        $this->parent = $parent;
        $this->priority = $priority ? $priority : 0;

        self::$chainCommands = is_array(self::$chainCommands) ? self::$chainCommands : array();
        array_push(self::$chainCommands, $this);

        if (false === $this->hasParent()) {
            $this->log(sprintf('%s is a master command of a command chain that has registered member commands', $this->getName()));
        } else {
            $this->log(sprintf('%s registered as a member of %s command chain', $this->getName(), $this->getParent()));
        }
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

        if (true === $this->hasParent()) {
            $this->log(sprintf('Executing %s chain members [%s]:', $this->getParent(), $this->getName()));
        } else {
            $this->log(sprintf('Executing %s command itself first:', $this->getName()));
        }
        $result = parent::run($input, $output);

        // execute children;
        $children = $this->getChildren();
        if ($children) {
            foreach ($children as $child) {
                $child->run($input, $output, true);
            }
        }

        if (false === $this->hasParent()) {
            $this->log(sprintf('Execution of %s chain completed.', $this->getName()));
        }

        return $result;
    }

    /**
     * @return void
     */
    public static function cleanChainCommands()
    {
        self::$chainCommands = array();
    }

    /**
     * @param string $parent
     *
     * @return AbstractChainCommand
     */
    public function setParent(string $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return string
     */
    public function getParent(): ?string
    {
        return $this->parent;
    }

    /**
     * @return bool
     */
    public function hasParent(): bool
    {
        return ($this->parent ? true : false);
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     */
    public function setPriority(int $priority)
    {
        $this->priority = $priority;
    }

    /**
     * @param string $message
     * @param int    $level
     */
    protected function log(string $message, $level = Logger::INFO)
    {
        if ($this->getLogger()) {
            $this->getLogger()->log($level, $message);
        }
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

    /**
     * @return Logger|null|object|\Symfony\Bridge\Monolog\Logger
     */
    private function getLogger()
    {
        if (!self::$logger) {
            self::$logger = new Logger('chainedLogger');
            self::$logger->pushHandler(new StreamHandler('./var/logs/chained.log', Logger::INFO));
        }

        return self::$logger;
    }
}
