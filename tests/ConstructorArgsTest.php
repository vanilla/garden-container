<?php
/**
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009-2017 Vanilla Forums Inc.
 * @license MIT
 */

namespace Garden\Container\Tests;


use Garden\Container\Container;
use Garden\Container\Reference;
use Garden\Container\Tests\Fixtures\Db;
use Garden\Container\Tests\Fixtures\Foo;
use Garden\Container\Tests\Fixtures\FooConsumer;
use Interop\Container\Exception\ContainerException;

class ConstructorArgsTest extends TestBase {
    /**
     * Named constructor args should work.
     */
    public function testNamedConstuctorArgs() {
        $c = new Container();

        $c->setConstructorArgs(['name' => 'foo']);

        /** @var Db $db */
        $db = $c->get(self::DB);
        $this->assertSame('foo', $db->name);
    }

    /**
     * A subclass should use constructor args from a base class.
     */
    public function testInheritedClass() {
        $c = new Container();

        $c
            ->rule(self::DB)
            ->setConstructorArgs(['foo']);

        /** @var Db $db */
        $db = $c->get(self::PDODB);
        $this->assertSame('foo', $db->name);
    }

    /**
     * A subclass should not use constructor args from a non-inherited base class.
     */
    public function testNonInheritedClass() {
        $c = new Container();

        $c
            ->rule(self::DB)
            ->setInherit(false)
            ->setConstructorArgs(['foo']);

        /** @var Db $db */
        $db = $c->get(self::PDODB);
        $this->assertNotSame('foo', $db->name);
    }

    /**
     * When setting an object constructor argument it should override a dependency injection.
     *
     * @param bool $shared Whether or not the container is shared.
     * @dataProvider provideShared
     */
    public function testConstructorArgsOverridingInjection($shared) {
        $dic = new Container();

        $db = new Db();

        $dic->rule(self::SQL)
            ->setShared(true)
            ->setConstructorArgs([$db]);

        /* @var Sql $sql */
        $sql = $dic->get(self::SQL);
        $this->assertSame($db, $sql->db);
    }

    /**
     * You should be able to provide constructor args to an interface.
     *
     * @param bool $shared Whether or not the container is shared.
     * @dataProvider provideShared
     */
    public function testInterfaceConstructorArgs($shared) {
        $dic = new Container();


        $dic->rule(self::DB_INTERFACE)
            ->setShared($shared)
            ->setConstructorArgs(['interface']);

        /* @var Db $db */
        $db = $dic->get(self::PDODB);
        $this->assertSame('interface', $db->name);
    }

    /**
     * Class constructor args should have a higher priority than interface constructor args.
     *
     */
    public function testInterfaceConstructorArgsPriority() {
        $dic = new Container();


        $dic->rule(self::DB_INTERFACE)
            ->setConstructorArgs(['interface'])

            ->rule(self::DB)
            ->setConstructorArgs(['class']);

        /* @var Db $db */
        $db = $dic->get(self::PDODB);
        $this->assertSame('class', $db->name);
    }

    /**
     * An interface that isn't marked to inherit should not inherit its constructor args.
     */
    public function testNonInheritedConstructorArgs() {
        $dic = new Container();

        $dic->rule(self::DB_INTERFACE)
            ->setInherit(false)
            ->setConstructorArgs(['interface']);

        /* @var Db $db */
        $db = $dic->get(self::PDODB);
        $this->assertSame('localhost', $db->name);
    }

    /**
     * A constructor with an interface hint should not fail if there is no rule for the interface.
     */
    public function testRulelessInterfaceHint() {
        $dic = new Container();

        /* @var \Garden\Container\Tests\Fixtures\DbDecorator $db */
        $db = $dic->get(self::DB_DECORATOR);

        $this->assertInstanceOf(self::DB_DECORATOR, $db);
        $this->assertSame('default', $db->db->name);
    }

    /**
     * A constructor with an interface hint should work when there is an instance for the interface.
     */
    public function testRulelessInterfaceHintWithInstance() {
        $dic = new Container();

        $dbInst = new Db('foo');
        $dic->setInstance(self::DB_INTERFACE, $dbInst);

        /* @var \Garden\Container\Tests\Fixtures\DbDecorator $db */
        $db = $dic->get(self::DB_DECORATOR);

        $this->assertInstanceOf(self::DB_DECORATOR, $db);
        $this->assertSame($dbInst, $db->db);
    }

    /**
     * A constructor with an interface hint should work when there is a rule for the interface.
     */
    public function testRulelessInterfaceHintWithRule() {
        $dic = new Container();

        $dic->rule(self::DB_INTERFACE)
            ->setClass(self::DB)
            ->setConstructorArgs(['rule']);

        /* @var \Garden\Container\Tests\Fixtures\DbDecorator $db */
        $db = $dic->get(self::DB_DECORATOR);

        $this->assertInstanceOf(self::DB_DECORATOR, $db);
        $this->assertSame('rule', $db->db->name);
    }

    /**
     * Shared classes should allow cyclic dependencies.
     */
    public function testCyclicSharedDependency() {
        $dic = new Container();

        $dic->rule(self::DB_DECORATOR)
            ->setShared(true)
            ->setConstructorArgs(['db' => new Reference(self::DB_DECORATOR)]);

        /* @var \Garden\Container\Tests\Fixtures\DbDecorator $db */
        $db = $dic->get(self::DB_DECORATOR);

        $this->assertSame($db, $db->db);
    }

    /**
     * Missing constructor parameters should throw an exception that can be understood.
     *
     * @param bool $shared Shared or factory construction.
     * @expectedException \Interop\Container\Exception\ContainerException
     * @dataProvider provideShared
     */
    public function testMissingRequiredParams($shared) {
        $dic = new Container();

        $m = $dic->get(FooConsumer::class);
    }

    /**
     * I should be able to pass a required parameter by name.
     *
     * @param bool $shared Shared or factory construction.
     * @dataProvider provideShared
     */
    public function testPassingRequiredParam($shared) {
        $dic = new Container();
        $foo = new Foo();

        $r = $dic->getArgs(FooConsumer::class, [$foo]);
        $this->assertSame($foo, $r->foo);
    }
}
