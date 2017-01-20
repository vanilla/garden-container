<?php
/**
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009-2017 Vanilla Forums Inc.
 * @license MIT
 */

namespace Garden\Container\Tests;


use Garden\Container\Container;
use Garden\Container\Tests\Fixtures\Db;

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
}
