<?php
/**
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009-2017 Vanilla Forums Inc.
 * @license MIT
 */

namespace Garden\Container;

use Interop\Container\Exception\ContainerException as InteropContainerException;
use Psr\Container\ContainerExceptionInterface;

/**
 * Represents the base exception for all {@link Container} exceptions.
 */
class ContainerException extends \Exception implements InteropContainerException, ContainerExceptionInterface {

}
