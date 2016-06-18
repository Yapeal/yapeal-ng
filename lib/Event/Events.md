Events.md
=========

Below are some of the details about the Yapeal event system and how it can be used.

## Background

First have a look at
[Event-driven programming](http://en.wikipedia.org/wiki/Event-driven_programming)
if you aren't sure what events are and how they are commonly used in programming. Yapeal's event system is a thin
wrapper around
[event-mediator](https://github.com/Dragonrun1/event-mediator)
which I developed to be an improvement (IMHO) over other publicly available ones.
I'll assume in the rest of this text that you've read the above links and understand how
events work and I will only explain the details of what Yapeal has added to event-mediator.

## Classes

Below I'll do a brief overview of the classes and interfaces used in Yapeal.

### EveApiEvent

`EveApiEvent` is extended from the base `Event` class and basically acts as an event wrapper around `EveAPiXmlData`
which you can access using `getData()` and `setData()`. This class implements the `EveApiEventInterface`. You will NOT
normally need to create instances of this class directly but will be receive them from Yapeal instead.

### EveApiEventInterface

This is a simple interface that extends from the `EventInterface` of event-mediator and allow for future transparent
changes to the underlying classes. It defines the `getData()` and `setData()` required methods.

### Mediator

This class extends from the `PimpleContainerMediator` class and implements the `MediatorInterface`. It add the
`triggerEveApiEvent()` which returns an `EveApiEvent` object vs the default `dispatch()` method that return a
generic `Event` object instead. It also provides `triggerLogEvent()` which is used for all error and other logging
events used in Yapeal.

### MediatorInterface

Interface that extends event-mediator's `ContainerMediatorInterface` and adds
`triggerEveApiEvent()` and `triggerLogEvent()` methods.

## Event Overview

There are two groups of events that you will see in Yapeal: Log, EveApi.

### Log Overview

The log events are present throughout the code at points where something that might be interesting is going on from a
database query to the receiving of an EveApi event by a registered subscriber. Log events have eight levels from
DEBUG(100) at the lowest level to EMERGENCY(600) at the highest. These are the some levels as seen in any UNIX type
system which where adopted by the PSR-3 standard as well. Yapeal only uses DEBUG through ALERT(550) with most be from
the lower end vs the higher. By default Yapeal will only write any of the messages to the log/yapeal.log file if a
WARNING(300) or higher level log event happens. Yapeal uses a threshold base log system so once that threshold is cross
it will continue to log everything received plus the preceding 25 messages before the triggering event.

Ignoring the all the varies logging events used throughout Yapeal the only other events that Yapeal emits are Eve API
related ones having to do with the varies stages of the processing. In the future other events maybe add if the need
arises. The following is a list of the Eve API events that are currently emitted when Yapeal is ran.

- Yapeal.EveApi.start - Occurs when per Eve API class is first called but before doing anything. Can be used for once per Eve
  API initializing etc. By default in Yapeal the per Eve API section classes handle this event.
- Yapeal.EveApi.retrieve - Different from above start event for account, char, and
    corp sections where it occurs on each key, char, corp, etc combo. By default Yapeal handles these with filesystem
    cache and network API retrievers.
- Yapeal.EveApi.transform - Occurs once the raw XML data is actually retrieved but before any xslt or other transforms have
    been applied. Yapeal use this to apply all of it's normal xslt transforms to make processing easier.
- Yapeal.EveApi.validate - Occurs after things like xslt transforms have changed the raw XML but before the transformed XML
    as been validated using XSD etc. Yapeal's XSD validator is attached to this event.
- Yapeal.Xml.error - Event occurs only when Yapeal determines XML contains error message and the cache interval has been
    updated on the `EveAPiXmlData` from the received validate event for any errors that Yapeal understands. __Good place
    to set `isActive = false` for a key or other wise manage keys etc depending on type of error that the application
    developer thinks should be done.__ Yapeal has no handlers attach to this event it here just for application
    developer use.
- Yapeal.EveApi.cache - Occurs before any of the database preservers are called. __NOTE: Can contain either XML data or
    error message at this point as both are considered valid results. Only occurs if XML is valid.__ Can be used as post
    `Yapeal.EveApi.validate` event. Yapeal uses this for filesystem cache of the Eve API XML but could be used for
    things like RSS feed etc.
- Yapeal.EveApi.preserve - Yapeal by default attaches all the per Eve API classes to the event which save them to the
    database. __NOTE: Like retrieve this is per combo for account, char, corp sections.__
- Yapeal.EveApi.end - Event only occurs if the 'retrieve', 'transform', 'validate', 'cache', and 'preserve' event are
    handled successfully. __NOTE: This is per combo for account, char, corp sections event as well.__

Just to be clear all the events from retrieve to end can be issued multiple times in the account, char, and corp
sections and also in eve/CharacterInfo if you have multiple keys, chars, or corps etc active for those Eve APIs.

## Example

Here a simple test example of how to use one of the new events.

    #!/usr/bin/env php
    <?php
    namespace Yapeal;

    require_once __DIR__ . '/bin/bootstrap.php';
    use Psr\Log\LoggerInterface;
    use Yapeal\Container\PimpleContainer;
    use Yapeal\Event\EveApiEventInterface;
    use Yapeal\Event\YapealEventDispatcherInterface;

    $dic = new PimpleContainer();
    $yapeal = new Yapeal($dic);
    $yapeal->wire($dic);
    /**
     * @var YapealEventDispatcherInterface $yem
     */
    $yem = $dic['Yapeal.Event.Dispatcher'];
    $test = function (
        EveApiEventInterface $event,
        $eventName,
        YapealEventDispatcherInterface $yem
    ) {
        $data = $event->getData();
        $mess = 'Received event ' . $eventName . ' for Eve API '
                . $data->getEveApiSectionName() . '/' . $data->getEveApiName()
                . PHP_EOL;
        print $mess;
    };
    $yem->addListener('eve_api.pre_retrieve', $test);
    exit($yapeal->autoMagic());

This example uses a function for the callable but I would expect you to be use a
class method in actual production code of course so more like this:
`$yem->addListener('eve_api.pre_retrieve', ['myAppClass', 'myHandler']);`
