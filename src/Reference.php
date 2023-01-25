<?php
/**
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009-2017 Vanilla Forums Inc.
 * @license MIT
 */

namespace Garden\Container;

/**
 * A reference to another entry in a {@link Container}.
 */
class Reference implements ReferenceInterface
{
    /**
     * @var string|array
     */
    private string|array $name;

    /**
     * @var array
     */
    private array $args;

    /**
     * Construct a new instance of the {@link Reference} class.
     *
     * @param array|string $name The name of the reference.
     * @param array $args Constructor arguments for the reference.
     */
    public function __construct(array|string $name, array $args = [])
    {
        $this->setName($name);
        $this->setArgs($args);
    }

    /**
     * Get the name of the reference.
     *
     * @return string|array Returns the name of the reference.
     */
    public function getName(): array|string
    {
        return $this->name;
    }

    /**
     * Set the name of the reference.
     *
     * @param array|string $name The name of the reference.
     */
    public function setName(array|string $name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Container $container, mixed $instance = null)
    {
        if (empty($this->name)) {
            return null;
        } elseif (is_string($this->name)) {
            return $container->getArgs($this->name, $this->getArgs());
        } else {
            $result = $container;
            foreach ($this->name as $name) {
                $result = $result->get($name);
            }
            return $result;
        }
    }

    /**
     * Get constructor arguments for the the reference.
     *
     * @return array Returns the arguments.
     */
    public function getArgs(): array
    {
        return $this->args;
    }

    /**
     * Set constructor arguments for the the reference.
     *
     * @param array $args An array of arguments.
     * @return $this
     */
    public function setArgs(array $args): static
    {
        $this->args = $args;
        return $this;
    }
}
