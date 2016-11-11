<?php
/**
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009-2016 Vanilla Forums Inc.
 * @license Proprietary
 */

namespace Garden\Container\Tests\Fixtures;


class Foo implements FooAwareInterface {
    public $foo;
    public $bar;

    // Do not add a constructor.


    function setFoo($foo) {
        $this->foo = $foo;
    }

    function setBar($bar) {
        $this->bar = $bar;
    }
}
