Events.md
=========

Below are some of the details about the Yapeal-ng event system and how it can be used.

## Background

First have a look at
[Event-driven programming](http://en.wikipedia.org/wiki/Event-driven_programming)
if you aren't sure what events are and how they are commonly used in programming. Yapeal-ng's event system is a thin
wrapper around
[event-mediator](https://github.com/Dragonrun1/event-mediator)
which I developed to be an improvement (IMHO) over other publicly available ones.
I'll assume in the rest of this text that you've read the above links and understand how
events work and I will only explain the details of what Yapeal-ng has added to event-mediator.

## Classes

Below I'll do a brief overview of the classes and interfaces used in Yapeal.

### EveApiEvent

`EveApiEvent` is extended from the base `Event` class and basically acts as an event wrapper around `EveApiXmlData`
which you can access using `getData()` and `setData()`. This class implements the `EveApiEventInterface`. You will NOT
normally need to create instances of this class directly but will be receive them from Yapeal-ng instead.

### EveApiEventInterface

This is a simple interface that extends from the `EventInterface` of event-mediator and allow for future transparent
changes to the underlying classes. It defines the `getData()` and `setData()` required methods.

### Mediator

This class extends from the `PimpleContainerMediator` class and implements the `MediatorInterface`. It add the
`triggerEveApiEvent()` which returns an `EveApiEvent` object vs the default `dispatch()` method that return a
generic `Event` object instead. It also provides `triggerLogEvent()` which is used for all error and other logging
events used in Yapeal-ng.

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
the lower end vs the higher. By default Yapeal-ng will only write any of the messages to the log/yapeal.log file if a
WARNING(300) or higher level log event happens. Yapeal-ng uses a threshold base log system so once that threshold is
cross it will continue to log everything received plus the preceding 25 messages before the triggering event.

Ignoring the all the varies logging events used throughout Yapeal-ng the only other events that Yapeal emits are Eve API
related ones having to do with the varies stages of the processing. These events drive all the action in Yapeal-ng and
form the heart of what it does. In the future other events maybe add if the need arises. The following is a list of the
Eve API events that are currently emitted when Yapeal is ran.

- Yapeal.EveApi.start - Occurs when per Eve API class is first called but before doing anything. Can be used for once
    per Eve API initializing etc. By default in Yapeal the per Eve API section classes handle this event and trigger the
    following retrieve, transform, validate, preserve, and end events as part of their process.
- Yapeal.EveApi.retrieve - Different from above start event for account, char, and corp sections where it occurs on each
    key, char, corp, etc combo. By default Yapeal handles these with filesystem cache and network Eve API retrievers.
- Yapeal.EveApi.transform - Occurs once the raw XML data is actually retrieved but before any xslt or other transforms
    have been applied. Yapeal use this to apply all of it's normal xslt transforms to make processing easier.
- Yapeal.EveApi.validate - Occurs after things like xslt transforms have changed the raw XML but before the transformed
    XML as been validated using XSD etc. Yapeal's XSD validator is attached to this event. The validator is responsible
    for triggering both of the following Yapeal.Xml.Error.* events during it's normal processing.
- Yapeal.Xml.Error.start - Event occurs only when Yapeal-ng determines XML contains error element instead of normal
    result element.  Yapeal-ng has a registered class that that uses the error code and some parts of the messages to
    set the cache interval on the `EveAPiXmlData` from the received event. __NOTE: This event would be a good place for
    application developers to register their own code to deactivate a registered key using `isActive = false` or
    otherwise manage keys etc that they think should be done.__ Application developers _must_ set their code to happen
    before the built-in code but not use either eventHandled() or setHandledSufficiently() except if they plan to
    replace to built-in handler's management of the cache interval. It is suggested you register for one or more of the
    account, char, or corp section level events instead of directly to this event which can also be triggered during
    Api, Eve, Map, and Server API where additional management is unlikely to be needed. See the
    [EveApiEventEmitterTrait::emitEvents() method](../../../lib/Event/EveApiEventEmitterTrait.php) for details about the
    cluster of events that are triggered.
- Yapeal.Xml.Error.preserve - The validator triggers this event for any received XML that is found to be invalid in some
    way. __NOTE: The Eve API name will be prefixed with `Invalid_` during this call.__ This is to allow it to be easily
    cached to the filesystem for debugging/troubleshooting without interfering with or being overwritten by any valid
    data. Yapeal-ng has a filesystem cache preserver registered for this event.
- Yapeal.EveApi.preserve - Yapeal by default registers all the per Eve API classes to this event cluster so they can
    save the XML data to the database. __NOTE: Like retrieve this is per combo for account, char, corp sections.__
- Yapeal.EveApi.Raw.preserve - Only triggered by some internal Yapeal-ng developer tools related to new Eve API class
    generation and never seen during normal processing. Yapeal-ng has the filesystem preserver registered for this event
    to capture `Raw_` XML data to keep it out of the way of normal cleaned up, transformed, and validated XML data.
- Yapeal.EveApi.end - Event only occurs if the 'retrieve', 'transform', 'validate', and 'preserve' event are handled
    successfully. __NOTE: This is per combo for account, char, corp sections event as well.__ Yapeal-ng doesn't register
    anything to this event and it is trigger just for application developer use.

Just to be clear all the events from 'retrieve' to 'end' can be issued multiple times in the account, char, and corp
sections and also in eve/CharacterInfo if you have multiple registered account, char, or corp keys in Yapeal-ng that are
active.

## Example

Here a simple test example of how to use one of the new events.

```php
#!/usr/bin/env php
<?php
declare(strict_types = 1);
namespace Yapeal;

require_once __DIR__ . '/bin/bootstrap.php';
use Yapeal\Configuration\Wiring;
use Yapeal\Container\Container;
use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\MediatorInterface;
use Yapeal\Log\Logger;
use Yapeal\Sql\CommonSqlQueries;
use Yapeal\Xml\EveApiReadWriteInterface;

$dic = new Container();
(new Wiring($dic))->wireAll();
// Application developer code.
$test = function (
    EveApiEventInterface $event,
    $eventName,
    MediatorInterface $yem
) {
    $data = $event->getData();
    $mess = 'Received event '
        . $eventName
        . ' for Eve API '
        . $data->getEveApiSectionName()
        . '/'
        . $data->getEveApiName()
        . PHP_EOL;
    $yem->triggerLogEvent('Yapeal.Log.log', Logger::INFO, $mess);
};
/**
 * @var CommonSqlQueries         $csq
 * @var EveApiReadWriteInterface $data
 * @var \PDO                     $pdo
 * @var MediatorInterface        $yem
 */
$csq = $dic['Yapeal.Sql.CommonQueries'];
$data = $dic['Yapeal.Xml.Data'];
$pdo = $dic['Yapeal.Sql.Connection'];
$yem = $dic['Yapeal.Event.Mediator'];
// Actually registers $test to start receiving events.
$yem->addListener('Yapeal.EveApi.retrieve', $test);
$yapeal = new Yapeal($csq, $data, $pdo, $yem);
exit($yapeal->autoMagic());
```

This example uses a function for the callable but I would expect you would
actually be using a class method in production code of course so more like
this:
`$yem->addListener('Yapeal.EveApi.retrieve', ['myAppClass', 'myHandler'], 'first');`
