<?php
/**
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009-2016 Vanilla Forums Inc.
 * @license Proprietary
 */

namespace Garden\Container\Tests;


abstract class TestBase extends \PHPUnit_Framework_TestCase {
    const DB = 'Garden\Container\Tests\Fixtures\Db';
    const FOO = 'Garden\Container\Tests\Fixtures\Foo';
    const FOO_AWARE = 'Garden\Container\Tests\Fixtures\FooAwareInterface';
    const PDODB = 'Garden\Container\Tests\Fixtures\PdoDb';
    const SQL = 'Garden\Container\Tests\Fixtures\Sql';
    const TUPLE = 'Garden\Container\Tests\Fixtures\Tuple';

    /**
     * Provide values for tests that are configured for shared and non shared.
     *
     * @return array Returns a data provider array.
     */
    public function provideShared() {
        return [
            'notShared' => [false],
            'shared' => [true]
        ];
    }
}
