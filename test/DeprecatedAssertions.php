<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\EventManager;

use PHPUnit\Framework\Assert;
use ReflectionClass;
use ReflectionObject;
use ReflectionProperty;
use stdClass;

use function get_class;
use function property_exists;
use function sprintf;

trait DeprecatedAssertions
{
    /**
     * @param mixed $value
     */
    public static function assertAttributeEmpty(
        string $attributeName,
        object $instance,
        string $message = ''
    ): void {

        if (! self::propertyExists($instance, $attributeName)) {
            Assert::fail(sprintf(
                'Failed to assert attribute %s is empty; attribute does not exist in instance of %s',
                $attributeName,
                get_class($instance)
            ));
        }

        Assert::assertEmpty(self::getPropertyValue($instance, $attributeName), $message);
    }

    /**
     * @param mixed $value
     */
    public static function assertAttributeEquals(
        $value,
        string $attributeName,
        object $instance,
        string $message = ''
    ): void {

        if (! self::propertyExists($instance, $attributeName)) {
            Assert::fail(sprintf(
                'Failed to assert equality against attribute %s; attribute does not exist in instance of %s',
                $attributeName,
                get_class($instance)
            ));
        }

        Assert::assertEquals($value, self::getPropertyValue($instance, $attributeName), $message);
    }

    /**
     * @param mixed $value
     */
    public static function assertAttributeInstanceOf(
        string $type,
        string $attributeName,
        object $instance,
        string $message = ''
    ): void {

        if (! self::propertyExists($instance, $attributeName)) {
            Assert::fail(sprintf(
                'Failed to assert type of attribute %s; attribute does not exist in instance of %s',
                $attributeName,
                get_class($instance)
            ));
        }

        Assert::assertInstanceOf($type, self::getPropertyValue($instance, $attributeName), $message);
    }

    /**
     * @param mixed $value
     */
    public static function assertAttributeSame(
        $value,
        string $attributeName,
        object $instance,
        string $message = ''
    ): void {

        if (! self::propertyExists($instance, $attributeName)) {
            Assert::fail(sprintf(
                'Failed to assert equality against attribute %s; attribute does not exist in instance of %s',
                $attributeName,
                get_class($instance)
            ));
        }

        Assert::assertSame($value, self::getPropertyValue($instance, $attributeName), $message);
    }

    /**
     * @return mixed
     */
    private static function getPropertyValue(object $instance, string $property)
    {
        if ($instance instanceof stdClass) {
            return $instance->$property;
        }

        $r = new ReflectionClass($instance);

        do {
            if ($r->hasProperty($property)) {
                $propertyReflection = $r->getProperty($property);
                $propertyReflection->setAccessible(true);
                return $propertyReflection->getValue($instance);
            }
        } while ($r = $r->getParentClass());

        return null;
    }

    private static function propertyExists(object $instance, string $property): bool
    {
        if (property_exists($instance, $property)) {
            return true;
        }

        $r = new ReflectionClass($instance);

        while ($r = $r->getParentClass()) {
            if ($r->hasProperty($property)) {
                return true;
            }
        }

        return false;
    }
}
