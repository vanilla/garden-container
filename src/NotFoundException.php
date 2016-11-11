<?php
/**
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009-2016 Vanilla Forums Inc.
 * @license MIT
 */

namespace Garden\Container;

/**
 * The exception thrown when an item isn't found in a {@link Container}.
 */
class NotFoundException extends ContainerException implements \Interop\Container\Exception\NotFoundException {

}
