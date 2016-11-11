<?php
/**
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009-2016 Vanilla Forums Inc.
 * @license MIT
 */

namespace Garden\Container;

/**
 * Used internally.
 */
class DefaultReference implements ReferenceInterface {
    /**
     * @var string
     */
    private $name;

    /**
     * Construct a new instance of the {@link Reference} class.
     *
     * @param string $name The name of the reference.
     */
    public function __construct($name) {
        $this->setName($name);
    }

    /**
     * Get the name of the reference.
     *
     * @return string Returns the name of the reference.
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set the name of the reference.
     *
     * @param string $name The name of the reference.
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Container $container, $_ = null) {
        return $container->get($this->name);
    }
}
