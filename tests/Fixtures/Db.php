<?php
/**
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009-2017 Vanilla Forums Inc.
 * @license MIT
 */

namespace Garden\Container\Tests\Fixtures;


class Db implements DbInterface {
    public $name;

    public function __construct($name = 'localhost') {
        $this->name = $name;
    }
}
