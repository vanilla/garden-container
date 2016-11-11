<?php
/**
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009-2016 Vanilla Forums Inc.
 * @license MIT
 */

namespace Garden\Container;


class DefaultReference implements ReferenceInterface {
    /**
     * @var array
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

    public function getName() {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    public function resolve(Container $container, $_ = null) {
        return $container->get($this->name);
    }
}
