<?php
/**
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009-2017 Vanilla Forums Inc.
 * @license MIT
 */

namespace Garden\Container\Tests;

use Garden\Container\Container;
use Garden\Container\Reference;
use Garden\Container\Tests\Fixtures\Model;
use Garden\Container\Tests\Fixtures\Sql;

/**
 * Tests involving reference instantiation.
 */
class ReferenceTest extends TestBase {

    /**
     * References can take arguments.
     */
    public function testReferenceArgs() {
        $dic = new Container();

        $dic->rule(Model::class)
            ->setConstructorArgs(['sql' => new Reference(Sql::class, ['baz'])]);

        /* @var Model $model */
        $model = $dic->get(Model::class);
        $this->assertSame('baz', $model->sql->name);
    }
}
