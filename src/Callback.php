<?php
/**
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009-2016 Vanilla Forums Inc.
 * @license MIT
 */

namespace Garden\Container;


class Callback implements ReferenceInterface {
    /**
     * @var callable $callback
     */
    private $callback;

    public function __construct(callable $callback) {
        $this->callback = $callback;
    }

    public function resolve(Container $container, $instance = null) {
        return call_user_func($this->callback, $container, $instance);
    }

    /**
     * Get the callback.
     *
     * @return callable Returns the callback.
     */
    public function getCallback() {
        return $this->callback;
    }

    /**
     * Set the callback.
     *
     * @param callable $callback The new callback to set.
     * @return Callback Returns `$this` for fluent calls.
     */
    public function setCallback($callback) {
        $this->callback = $callback;
        return $this;
    }
}
