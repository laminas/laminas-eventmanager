<?php

declare(strict_types=1);

namespace LaminasTest\EventManager;

use PHPUnit\Framework\Assert;
use ReflectionClass;
use stdClass;

use function property_exists;
use function sprintf;

// phpcs:ignore WebimpressCodingStandard.NamingConventions.Trait.Suffix
trait DeprecatedAssertions
{
    /**
     * @param non-empty-string $attributeName
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
                $instance::class
            ));
        }

        Assert::assertEmpty(self::getPropertyValue($instance, $attributeName), $message);
    }

    /**
     * @param mixed $value
     * @param non-empty-string $attributeName
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
                $instance::class
            ));
        }

        Assert::assertEquals($value, self::getPropertyValue($instance, $attributeName), $message);
    }

    /**
     * @param non-empty-string $attributeName
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
                $instance::class
            ));
        }

        Assert::assertInstanceOf($type, self::getPropertyValue($instance, $attributeName), $message);
    }

    /**
     * @param mixed $value
     * @param non-empty-string $attributeName
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
                $instance::class
            ));
        }

        Assert::assertSame($value, self::getPropertyValue($instance, $attributeName), $message);
    }

    /**
     * @param non-empty-string $property
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
                return $propertyReflection->getValue($instance);
            }
        } while ($r = $r->getParentClass());

        return null;
    }

    /** @param non-empty-string $property */
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
