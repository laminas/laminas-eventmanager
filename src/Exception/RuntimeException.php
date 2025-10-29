<?php

namespace Laminas\EventManager\Exception;

use RuntimeException as SplRuntimeException;

/**
 * Runtime exception
 *
 * @final This class should not be extended
 */
class RuntimeException extends SplRuntimeException implements ExceptionInterface
{
}
