<?php

declare(strict_types=1);

use Boundwize\StructArmed\Architecture;
use Boundwize\StructArmed\Preset\Preset;

return Architecture::define()
    ->withPresets(Preset::PSR4(), Preset::CODEQUALITY())
    ->layer('Exception', 'src/Exception')
    ->layer('Response', 'src/ResponseCollection.php')
    ->layer('Event', [
        'src/Event.php',
        'src/EventInterface.php',
    ])
    ->layer('SharedEventManager', [
        'src/SharedEventManager.php',
        'src/SharedEventManagerInterface.php',
        'src/SharedEventsCapableInterface.php',
    ])
    ->layer('EventManager', [
        'src/EventManager.php',
        'src/EventManagerInterface.php',
        'src/EventManagerAwareInterface.php',
        'src/EventManagerAwareTrait.php',
        'src/EventsCapableInterface.php',
    ])
    ->layer('ListenerAggregate', [
        'src/AbstractListenerAggregate.php',
        'src/ListenerAggregateInterface.php',
        'src/ListenerAggregateTrait.php',
    ])
    ->layer('LazyListener', [
        'src/LazyListener.php',
        'src/LazyEventListener.php',
        'src/LazyListenerAggregate.php',
    ])
    ->layer('Filter', ['src/FilterChain.php', 'src/Filter'])
    ->layer('Test', 'src/Test')
    ->ruleset([
        'Exception'          => [],
        'Response'           => [],
        'Event'              => ['Exception'],
        'SharedEventManager' => ['Exception'],
        'EventManager'       => ['+Event', '+SharedEventManager', 'Response'],
        'ListenerAggregate'  => ['+EventManager'],
        'LazyListener'       => ['+ListenerAggregate'],
        'Filter'             => ['Exception', 'Response'],
        'Test'               => ['+EventManager'],
    ]);
