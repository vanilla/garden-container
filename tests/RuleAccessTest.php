<?php
/**
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009-2016 Vanilla Forums Inc.
 * @license MIT
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

    /**
     * A new container should have the default (*) rule.
     */
    public function testHasDefaultRule() {
        $c = new Container();

        $this->assertTrue($c->hasRule('*'));
    }

    /**
     * A defined rule should test for existence.
     */
    public function testHasRule() {
        $c = new Container();

        $this->assertFalse($c->hasRule('foo'));

        $c->rule('foo')->setClass(self::DB);
        $this->assertTrue($c->hasRule('foo'));
    }

    /**
     * A newly defined rule should not test as true.
     */
    public function testNotHasNewSelectedRule() {
        $c = new Container();

        $this->assertFalse($c->hasRule('foo'));

        $c->rule('foo');
        $this->assertFalse($c->hasRule('foo'));
    }

    /**
     * A subclass should not say it has a rule if its parent has a rule defined.
     */
    public function testSubclassHasRule() {
        $c = new Container();

        $c->rule(self::DB)->setClass(self::PDODB);

        $this->assertFalse($c->hasRule(self::PDODB));
    }
}
