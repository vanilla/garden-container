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
class Reference implements ReferenceInterface {
    /**
     * @var string|array
     */
    private $name;

    /**
     * Construct a new instance of the {@link Reference} class.
     *
     * @param string|array $name The name of the reference.
     */
    public function __construct($name) {
        $this->setName($name);
    }

    /**
     * Get the name of the reference.
     *
     * @return string|array Returns the name of the reference.
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set the name of the reference.
     *
     * @param string|array $name The name of the reference.
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Container $container, $_ = null) {
        if (empty($this->name)) {
            return null;
        } elseif (is_string($this->name)) {
            return $container->get($this->name);
        } else {
            $result = $container;
            foreach ($this->name as $name) {
                $result = $result->get($name);
            }
            return $result;
        }
    }
}
