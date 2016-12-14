<?php
/**
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009-2016 Vanilla Forums Inc.
 * @license Proprietary
 */

namespace Garden\Container\Tests;


use Garden\Container\Container;

class RuleAccessTest extends TestBase {
    /**
     * A new container's default rule should have sensible defaults.
     */
    public function testDefaultRule() {
        $c = new Container();

        $this->assertSame('', $c->getClass());
        $this->assertSame(true, $c->getInherit());
        $this->assertSame(false, $c->isShared());
        $this->assertSame([], $c->getConstructorArgs());
        $this->assertNull($c->getFactory());

        return $c;
    }

    /**
     * Getters and setters should work.
     *
     * @param Container $c A default container.
     * @depends testDefaultRule
     */
    public function testGettersSetters(Container $c) {
        $c->setClass(self::FOO);
        $this->assertSame(self::FOO, $c->getClass());

        $c->setInherit(false);
        $this->assertSame(false, $c->getInherit());

        $c->setShared(true);
        $this->assertSame(true, $c->isShared());

        $arr = [123];
        $c->setConstructorArgs($arr);
        $this->assertSame($arr, $c->getConstructorArgs());

        $callback = function () {
            return 'foo';
        };
        $c->setFactory($callback);
        $this->assertSame($callback, $c->getFactory());
    }
}
