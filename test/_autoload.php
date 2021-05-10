<?php

use PHPUnit\Framework\ExpectationFailedException;

if (class_exists('PHPUnit_Framework_ExpectationFailedException')) {
    class_alias('PHPUnit_Framework_ExpectationFailedException', ExpectationFailedException::class);
}
