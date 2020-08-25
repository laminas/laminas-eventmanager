<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\EventManager;

use Laminas\EventManager\Event;
use Laminas\EventManager\Exception;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Stdlib
 */
class EventTest extends TestCase
{

    public function testConstructorWithArguments()
    {
        $name = 'foo';
        $target = 'bar';
        $params = ['test','param'];

        $event = new Event($name, $target, $params);

        self::assertEquals($name, $event->getName());
        self::assertEquals($target, $event->getTarget());
        self::assertEquals($params, $event->getParams());
    }

    public function testSetParamsWithInvalidParameter()
    {
        $event = new Event('foo');
        $this->expectException(Exception\InvalidArgumentException::class);
        $event->setParams('test');
    }

    public function testGetParamReturnsDefault()
    {
        $event = new Event('foo', 'bar', []);
        $default = 1;

        self::assertEquals($default, $event->getParam('foo', $default));
    }

    public function testGetParamReturnsDefaultForObject()
    {
        $params = new \stdClass();
        $event = new Event('foo', 'bar', $params);
        $default = 1;

        self::assertEquals($default, $event->getParam('foo', $default));
    }

    public function testGetParamReturnsForObject()
    {
        $key = 'test';
        $value = 'value';
        $params = new \stdClass();
        $params->$key = $value;

        $event = new Event('foo', 'bar', $params);
        $default = 1;

        self::assertEquals($value, $event->getParam($key));
    }
}
