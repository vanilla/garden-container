<?php
/**
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009-2016 Vanilla Forums Inc.
 * @license MIT
 */

namespace Garden\Container\Tests;

use Garden\Container\Callback;
use Garden\Container\Container;
use Garden\Container\DefaultReference;
use Garden\Container\Reference;
use Garden\Container\Tests\Fixtures\Db;
use Garden\Container\Tests\Fixtures\Sql;
use Garden\Container\Tests\Fixtures\Tuple;

class ContainerTest extends TestBase {
    public function testBasicConstruction() {
        $c = new Container();
        $db = $c->get(self::DB);

        $this->assertInstanceOf(self::DB, $db);
    }

    public function testNotSharedConstruction() {
        $c = new Container();

        $db1 = $c->get(self::DB);
        $db2 = $c->get(self::DB);
        $this->assertNotSame($db1, $db2);
    }

    public function testSharedConstuction() {
        $c = new Container();

        $c->setShared(true);
        $db1 = $c->get(self::DB);
        $db2 = $c->get(self::DB);
        $this->assertSame($db1, $db2);
    }

    public function testConstrucWithPassedArgs() {
        $c = new Container();

        /** @var Db $db */
        $db = $c->getArgs(self::DB, ['foo']);
        $this->assertSame('foo', $db->name);
    }

    public function testConstructDifferentClass() {
        $c = new Container();

        $c
            ->rule(self::DB)
            ->setClass(self::PDODB);

        $db = $c->get(self::DB);
        $this->assertInstanceOf(self::DB, $db);

    }

    public function testBaicInjection() {
        $c = new Container();

        /** @var Sql $sql */
        $sql = $c->get(self::SQL);
        $this->assertInstanceOf(self::DB, $sql->db);
    }

    public function testSetInstance() {
        $c = new Container();

        $db = new Db();

        $c->setInstance(get_class($db), $db);
        $db2 = $c->get(get_class($db));

        $this->assertSame($db, $db2);
    }

    /**
     * Test rule calls.
     *
     * @param bool $shared Whether the container should be shared.
     * @dataProvider provideShared
     */
    public function testCalls($shared) {
        $c = new Container();

        $c
            ->rule(self::TUPLE)
            ->setShared($shared)
            ->setConstructorArgs(['a', 'b'])
            ->addCall('setA', ['foo']);

        $t = $c->get(self::TUPLE);
        $this->assertSame('foo', $t->a);
        $this->assertSame('b', $t->b);
    }

    /**
     * Test instantiation on classes without constructors.
     *
     * @param bool $shared Whether the container should be shared.
     * @dataProvider provideShared
     */
    public function testNoConstructor($shared) {
        $c = new Container();

        $c->setShared($shared);

        $o = $c->get(self::FOO);
        $this->assertInstanceOf(self::FOO, $o);
    }

    public function testNestedReference() {
        $parent = new Container();
        $child = new Container();

        $parent->setInstance('config', $child);
        $child->setInstance('foo', 'bar');

        $parent
            ->rule(self::DB)
            ->setConstructorArgs([new Reference(['config', 'foo'])]);

        $db = $parent->get(self::DB);
        $this->assertSame('bar', $db->name);
    }

    /**
     * Test interface call rules.
     */
    public function testInterfaceCalls() {
        $c = new Container();

        $c->rule(self::FOO_AWARE)
            ->addCall('setFoo', [123]);

        $foo = $c->get(self::FOO);
        $this->assertSame(123, $foo->foo);
    }

    /**
     * Interface calls should stack with rule calls.
     */
    public function testInterfaceCallMerging() {
        $c = new Container();

        $c
            ->rule(self::FOO_AWARE)
            ->addCall('setFoo', [123])
            ->defaultRule()
            ->addCall('setBar', [456]);

        $foo = $c->get(self::FOO);
        $this->assertSame(123, $foo->foo);
        $this->assertSame(456, $foo->bar);
    }

    /**
     * A named arg should override injections.
     */
    public function testNamedInjectedArg() {
        $c = new Container();

        $db = new Db();
        /* @var Sql $sql */
        $sql = $c->getArgs(self::SQL, ['db' => $db]);
        $this->assertSame($db, $sql->db);
    }

    /**
     * A positional arg should override injections.
     */
    public function testPositionalInjectedArg() {
        $c = new Container();

        $db = new Db();
        /* @var Sql $sql */
        $sql = $c->getArgs(self::SQL, [$db]);
        $this->assertSame($db, $sql->db);
    }

    /**
     * Trying to get a non-existent, unset name should throw a not found exception.
     *
     * @param bool $shared Set the container to shared or not.
     * @dataProvider provideShared
     * @expectedException \Garden\Container\NotFoundException
     */
    public function testNotFoundException($shared) {
        $c = new Container();

        $o = $c
            ->setShared($shared)
            ->get('adsf');
    }

    public function testBasicCall() {
        $c = new Container();

        /**
         * @var Sql $sql
         */
        $sql = $c->getArgs(self::SQL, ['db' => null]);
        $this->assertNull($sql->db);

        $c->call([$sql, 'setDb']);
        $this->assertInstanceOf(self::DB, $sql->db);
    }

    public function testCallback() {
        $c = new Container();

        $i = 1;
        $cb = new Callback(function () use (&$i) {
            return $i++;
        });

        /**
         * @var Tuple $tuple
         */
        $tuple = $c->getArgs(self::TUPLE, [$cb, $cb]);

        $this->assertSame(1, $tuple->a);
        $this->assertSame(2, $tuple->b);
    }
}
