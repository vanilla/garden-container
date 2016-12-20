<?php
/**
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009-2016 Vanilla Forums Inc.
 * @license MIT
 */

namespace Garden\Container\Tests;


abstract class TestBase extends \PHPUnit_Framework_TestCase {
    const DB = 'Garden\Container\Tests\Fixtures\Db';
    const DB_INTERFACE = 'Garden\Container\Tests\Fixtures\DbInterface';
    const FOO = 'Garden\Container\Tests\Fixtures\Foo';
    const FOO_AWARE = 'Garden\Container\Tests\Fixtures\FooAwareInterface';
    const PDODB = 'Garden\Container\Tests\Fixtures\PdoDb';
    const SQL = 'Garden\Container\Tests\Fixtures\Sql';
    const TUPLE = 'Garden\Container\Tests\Fixtures\Tuple';

    private $errors;

    /**
     * Clear out the errors array.
     */
    protected function setUp() {
        $this->errors = [];
        set_error_handler([$this, "errorHandler"]);
    }

    /**
     * Track errors that occur during testing.
     *
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @param mixed $errcontext
     */
    public function errorHandler($errno, $errstr, $errfile, $errline, $errcontext) {
        $this->errors[] = compact("errno", "errstr", "errfile", "errline", "errcontext");
    }

    /**
     * Assert than an error has occurred.
     *
     * @param string $errstr The desired error string.
     * @param int $errno The desired error number.
     */
    public function assertError($errstr, $errno) {
        foreach ($this->errors as $error) {
            if ($error["errstr"] === $errstr
                && $error["errno"] === $errno) {
                return;
            }
        }
        $this->fail(
            "Error with level $errno and message '$errstr' not found in ",
            var_export($this->errors, true)
        );
    }

    /**
     * Assert than an error has occurred.
     *
     * @param int $errno The desired error number.
     */
    public function assertErrorNumber($errno) {
        foreach ($this->errors as $error) {
            if ($error["errno"] === $errno) {
                return;
            }
        }

        $arr = [E_USER_NOTICE => 'E_USER_NOTICE', E_USER_WARNING => 'E_USER_WARNING', E_USER_DEPRECATED => 'E_USER_DEPRECATED'];
        if (isset($arr[$errno])) {
            $errno = $arr[$errno];
        }

        $this->fail(
            "Error with level $errno not found in ",
            var_export($this->errors, true)
        );
    }

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
