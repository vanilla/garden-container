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
        $this->assertSame('', $c->getAliasOf());
        $this->assertSame([], $c->getAliases());

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

        $alias = 'bar';
        $c->setAliasOf($alias);
        $this->assertSame($alias, $c->getAliasOf());
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

    /**
     * Added aliases should be discoverable.
     */
    public function testAddAliases() {
        $dic = new Container();

        $dic->rule('foo')
            ->addAlias('bar')
            ->addAlias('baz');

        $this->assertEmpty(array_diff(['bar', 'baz'], $dic->getAliases()));
    }

    /**
     * Test the symmetry of {@link Container::addAlias()} and {@link Container::getAliasOf()}.
     */
    public function testAddAliasAndAliasOfSymmetry() {
        $dic = new Container();

        $dic->rule('foo')
            ->addAlias('bar');

        $this->assertSame('foo', $dic->rule('bar')->getAliasOf());
    }

    /**
     * Test the symmetry of {@link Container::setAliasOf()} and {@link Container::getAliases()}.
     */
    public function testSetAliasOfAndGetAliasesSymmetry() {
        $dic = new Container();

        $dic->rule('foo')
            ->setAliasOf('bar');

        $this->assertSame(['foo'], $dic->rule('bar')->getAliases());
    }

    /**
     * Setting an alias to yourself should do nothing and raise a notice.
     *
     * @expectedException \PHPUnit_Framework_Error_Notice
     */
    public function testSetSameAliasNotice() {
        $dic = new Container();

        $dic->rule('foo')
            ->setAliasOf('foo');
    }

    /**
     * Setting an alias to yourself should do nothing and raise a notice.
     *
     * @expectedException \PHPUnit_Framework_Error_Notice
     */
    public function testAddSameAliasNotice() {
        $dic = new Container();

        $dic->rule('foo')
            ->addAlias('foo');
    }

    /**
     * You should be able to remove a rule alias.
     */
    public function testRemoveAlias() {
        $dic = new Container();

        $dic->addAlias('foo');
        $this->assertSame(['foo'], $dic->getAliases());

        $dic->removeAlias('foo');
        $this->assertSame([], $dic->getAliases());
    }

    /**
     * Aliases should be normalized to exclude leading backslashes.
     */
    public function testAliasNormalization() {
        $dic = new Container();

        $dic->rule('foo')
            ->addAlias('\bar');

        $this->assertSame(['bar'], $dic->getAliases());
    }
}
