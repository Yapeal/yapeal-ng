<?php
declare(strict_types = 1);
/**
 * Contains class CacheRetrieverSpec.
 *
 * PHP version 7.0
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2016-2017 Michael Cummings
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Lesser General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License
 * for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see
 * <http://spdx.org/licenses/LGPL-3.0.html>.
 *
 * You should be able to find a copy of this license in the COPYING-LESSER.md
 * file. A copy of the GNU GPL should also be available in the COPYING.md file.
 *
 * @author    Michael Cummings <mgcummings@yahoo.com>
 * @copyright 2016-2017 Michael Cummings
 * @license   LGPL-3.0
 */
namespace Spec\Yapeal\FileSystem;

use PhpSpec\Exception\Example\FailureException;
use PhpSpec\ObjectBehavior;
use PhpSpec\Wrapper\Collaborator;
use Prophecy\Argument;
use Spec\Yapeal\FileSystemUtilTrait;
use Symfony\Component\Filesystem\Filesystem;
use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\EveApiRetrieverInterface;
use Yapeal\Event\LogEventInterface;
use Yapeal\Event\MediatorInterface;
use Yapeal\FileSystem\CacheRetriever;
use Yapeal\Log\Logger;
use Yapeal\Xml\EveApiXmlData;

/**
 * Class CacheRetrieverSpec
 *
 * @mixin \Yapeal\FileSystem\CacheRetriever
 *
 * @method void during($method, array $params)
 * @method void shouldBe($value)
 * @method void shouldContain($value)
 * @method void shouldNotEqual($value)
 * @method void shouldReturn($result)
 */
class CacheRetrieverSpec extends ObjectBehavior
{
    use FileSystemUtilTrait;
    /**
     * TransformerSpec constructor.
     */
    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }
    public function __destruct()
    {
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        @rmdir(dirname($this->workingDirectory));
    }
    public function it_is_initializable()
    {
        $this->shouldHaveType(CacheRetriever::class);
        $this->shouldImplement(EveApiRetrieverInterface::class);
    }
    /**
     * @param Collaborator|EveApiEventInterface $event
     * @param Collaborator|LogEventInterface    $log
     * @param Collaborator|MediatorInterface    $yem
     *
     * @throws FailureException
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Prophecy\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     * @throws \UnexpectedValueException
     */
    public function it_should_delete_expired_cache_file(
        EveApiEventInterface $event,
        LogEventInterface $log,
        MediatorInterface $yem
    ) {
        $xml = <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<eveapi version="1">
    <currentTime>2000-12-31 23:54:59</currentTime>
    <result />
    <cachedUntil>2000-12-31 23:59:59</cachedUntil>
</eveapi>
XML;
        $data = (new EveApiXmlData())->setEveApiName('Api1')
            ->setEveApiSectionName('Section1');
        $pathFile = sprintf($this->workingDirectory . 'cache/%s/%s%s.xml',
            $data->getEveApiSectionName(),
            $data->getEveApiName(),
            $data->getHash());
        $this->filesystem->dumpFile($pathFile, $xml);
        $event->getData()
            ->willReturn($data);
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent(Argument::cetera())
            ->willReturn($log);
        $this->retrieveEveApi($event, 'test', $yem);
        if ($this->filesystem->exists($pathFile)) {
            throw new FailureException('Failed to delete expired XML file');
        }
    }
    /**
     * @param Collaborator|EveApiEventInterface $event
     * @param Collaborator|MediatorInterface    $yem
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    public function it_should_do_nothing_when_should_retrieve_is_false(
        EveApiEventInterface $event,
        MediatorInterface $yem
    ) {
        $this->setRetrieve(false);
        $this->retrieveEveApi($event, 'test', $yem)
            ->shouldReturn($event);
    }
    /**
     * @param Collaborator|EveApiEventInterface $event
     * @param Collaborator|LogEventInterface    $log
     * @param Collaborator|MediatorInterface    $yem
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Prophecy\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     * @throws \UnexpectedValueException
     */
    public function it_should_log_error_when_any_date_time_instance_returns_false(
        EveApiEventInterface $event,
        LogEventInterface $log,
        MediatorInterface $yem
    ) {
        $xml = <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<eveapi version="1">
    <currentTime>Not a DT</currentTime>
    <result />
    <cachedUntil>2020-12-31 23:59:59</cachedUntil>
</eveapi>
XML;
        $data = (new EveApiXmlData())->setEveApiName('Api1')
            ->setEveApiSectionName('Section1');
        $pathFile = sprintf($this->workingDirectory . 'cache/%s/%s%s.xml',
            $data->getEveApiSectionName(),
            $data->getEveApiName(),
            $data->getHash());
        $this->filesystem->dumpFile($pathFile, $xml);
        $event->getData()
            ->willReturn($data);
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent(Argument::cetera())
            ->willReturn($log);
        $messagePrefix = 'Failed to get DateTime instance for "now" or currentTime or cachedUntil during the retrieval of';
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent('Yapeal.Log.log',
            Logger::ERROR,
            Argument::containingString($messagePrefix),
            Argument::cetera())
            ->willReturn($log)
            ->shouldBeCalled();
        $this->retrieveEveApi($event, 'test', $yem);
    }
    /**
     * @param Collaborator|EveApiEventInterface $event
     * @param Collaborator|LogEventInterface    $log
     * @param Collaborator|MediatorInterface    $yem
     *
     * @throws FailureException
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Prophecy\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     * @throws \UnexpectedValueException
     */
    public function it_should_log_message_when_xml_can_not_be_loaded(
        EveApiEventInterface $event,
        LogEventInterface $log,
        MediatorInterface $yem
    ) {
        $data = (new EveApiXmlData())->setEveApiName('Api1')
            ->setEveApiSectionName('Section1');
        $event->getData()
            ->willReturn($data);
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent(Argument::cetera())
            ->willReturn($log);
        $messagePrefix = 'Failed to retrieve XML file ';
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent('Yapeal.Log.log',
            Logger::INFO,
            Argument::containingString($messagePrefix),
            Argument::cetera())
            ->willReturn($log)
            ->shouldBeCalled();
        $this->retrieveEveApi($event, 'test', $yem);
    }
    /**
     * @param Collaborator|EveApiEventInterface $event
     * @param Collaborator|LogEventInterface    $log
     * @param Collaborator|MediatorInterface    $yem
     *
     * @throws FailureException
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Prophecy\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     * @throws \UnexpectedValueException
     */
    public function it_should_log_warning_including_exception_when_retrieving_invalid_xml(
        EveApiEventInterface $event,
        LogEventInterface $log,
        MediatorInterface $yem
    ) {
        $xml = '<?xml version="1.0" encoding="utf-8"?>';
        $data = (new EveApiXmlData())->setEveApiName('Api1')
            ->setEveApiSectionName('Section1');
        $pathFile = sprintf($this->workingDirectory . 'cache/%s/%s%s.xml',
            $data->getEveApiSectionName(),
            $data->getEveApiName(),
            $data->getHash());
        $this->filesystem->dumpFile($pathFile, $xml);
        $event->getData()
            ->willReturn($data);
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent(Argument::cetera())
            ->willReturn($log);
        $messagePrefix = 'The XML cause SimpleXMLElement exception during the retrieval of';
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent('Yapeal.Log.log',
            Logger::WARNING,
            Argument::containingString($messagePrefix),
            Argument::withEntry('exception', Argument::type(\Exception::class)))
            ->willReturn($log)
            ->shouldBeCalled();
        $this->retrieveEveApi($event, 'test', $yem);
    }
    /**
     * @param Collaborator|EveApiEventInterface $event
     * @param Collaborator|LogEventInterface    $log
     * @param Collaborator|MediatorInterface    $yem
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Prophecy\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     * @throws \UnexpectedValueException
     */
    public function it_should_log_warning_when_cached_until_element_is_missing(
        EveApiEventInterface $event,
        LogEventInterface $log,
        MediatorInterface $yem
    ) {
        $xml = <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<eveapi version="1">
    <currentTime>2020-12-31 23:54:59</currentTime>
    <result />
</eveapi>
XML;
        $data = (new EveApiXmlData())->setEveApiName('Api1')
            ->setEveApiSectionName('Section1');
        $pathFile = sprintf($this->workingDirectory . 'cache/%s/%s%s.xml',
            $data->getEveApiSectionName(),
            $data->getEveApiName(),
            $data->getHash());
        $this->filesystem->dumpFile($pathFile, $xml);
        $event->getData()
            ->willReturn($data);
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent(Argument::cetera())
            ->willReturn($log);
        $messagePrefix = 'Cached XML file missing required cachedUntil element during the retrieval of';
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent('Yapeal.Log.log',
            Logger::WARNING,
            Argument::containingString($messagePrefix),
            Argument::cetera())
            ->willReturn($log)
            ->shouldBeCalled();
        $this->retrieveEveApi($event, 'test', $yem);
    }
    /**
     * @param Collaborator|EveApiEventInterface $event
     * @param Collaborator|LogEventInterface    $log
     * @param Collaborator|MediatorInterface    $yem
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Prophecy\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     * @throws \UnexpectedValueException
     */
    public function it_should_log_warning_when_current_time_element_is_missing(
        EveApiEventInterface $event,
        LogEventInterface $log,
        MediatorInterface $yem
    ) {
        $xml = <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<eveapi version="1">
    <result />
    <cachedUntil>2020-12-31 23:59:59</cachedUntil>
</eveapi>
XML;
        $data = (new EveApiXmlData())->setEveApiName('Api1')
            ->setEveApiSectionName('Section1');
        $pathFile = sprintf($this->workingDirectory . 'cache/%s/%s%s.xml',
            $data->getEveApiSectionName(),
            $data->getEveApiName(),
            $data->getHash());
        $this->filesystem->dumpFile($pathFile, $xml);
        $event->getData()
            ->willReturn($data);
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent(Argument::cetera())
            ->willReturn($log);
        $messagePrefix = 'Cached XML file missing required currentTime element during the retrieval of';
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent('Yapeal.Log.log',
            Logger::WARNING,
            Argument::containingString($messagePrefix),
            Argument::cetera())
            ->willReturn($log)
            ->shouldBeCalled();
        $this->retrieveEveApi($event, 'test', $yem);
    }
    /**
     * @param Collaborator|EveApiEventInterface $event
     * @param Collaborator|LogEventInterface    $log
     * @param Collaborator|MediatorInterface    $yem
     *
     * @throws FailureException
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Prophecy\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     * @throws \UnexpectedValueException
     */
    public function it_should_log_warning_when_xml_is_empty(
        EveApiEventInterface $event,
        LogEventInterface $log,
        MediatorInterface $yem
    ) {
        $xml = '';
        $data = (new EveApiXmlData())->setEveApiName('Api1')
            ->setEveApiSectionName('Section1');
        $pathFile = sprintf($this->workingDirectory . 'cache/%s/%s%s.xml',
            $data->getEveApiSectionName(),
            $data->getEveApiName(),
            $data->getHash());
        $this->filesystem->dumpFile($pathFile, $xml);
        $event->getData()
            ->willReturn($data);
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent(Argument::cetera())
            ->willReturn($log);
        $messagePrefix = 'Received an empty XML file ';
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent('Yapeal.Log.log',
            Logger::WARNING,
            Argument::containingString($messagePrefix),
            Argument::cetera())
            ->willReturn($log)
            ->shouldBeCalled();
        $this->retrieveEveApi($event, 'test', $yem);
    }
    /**
     * @param Collaborator|EveApiEventInterface $event
     * @param Collaborator|LogEventInterface    $log
     * @param Collaborator|MediatorInterface    $yem
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Prophecy\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     * @throws \UnexpectedValueException
     */
    public function it_should_not_succeed_when_cached_until_is_expired_and_has_been_cached_more_than_five_minutes(
        EveApiEventInterface $event,
        LogEventInterface $log,
        MediatorInterface $yem
    ) {
        $xml = <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<eveapi version="1">
    <currentTime>2000-12-31 23:54:59</currentTime>
    <result />
    <cachedUntil>2000-12-31 23:59:59</cachedUntil>
</eveapi>
XML;
        $data = (new EveApiXmlData())->setEveApiName('Api1')
            ->setEveApiSectionName('Section1');
        $pathFile = sprintf($this->workingDirectory . 'cache/%s/%s%s.xml',
            $data->getEveApiSectionName(),
            $data->getEveApiName(),
            $data->getHash());
        $this->filesystem->dumpFile($pathFile, $xml);
        $event->getData()
            ->willReturn($data);
        $event->setHandledSufficiently()
            ->shouldNotBeCalled();
        $event->setData($data->setEveApiXml($xml))
            ->shouldNotBeCalled();
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent(Argument::cetera())
            ->willReturn($log);
        $messagePrefix = 'Successfully retrieved the XML of';
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent('Yapeal.Log.log',
            Logger::DEBUG,
            Argument::containingString($messagePrefix),
            Argument::cetera())
            ->willReturn($log)
            ->shouldNotBeCalled();
        $this->retrieveEveApi($event, 'test', $yem);
    }
    /**
     * @param Collaborator|EveApiEventInterface $event
     * @param Collaborator|LogEventInterface    $log
     * @param Collaborator|MediatorInterface    $yem
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Prophecy\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     * @throws \UnexpectedValueException
     */
    public function it_should_succeed_when_cached_until_is_not_expired_and_has_been_cached_more_than_five_minutes(
        EveApiEventInterface $event,
        LogEventInterface $log,
        MediatorInterface $yem
    ) {
        $xml = <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<eveapi version="1">
    <currentTime>2000-12-31 23:54:59</currentTime>
    <result />
    <cachedUntil>2020-12-31 23:59:59</cachedUntil>
</eveapi>
XML;
        $data = (new EveApiXmlData())->setEveApiName('Api1')
            ->setEveApiSectionName('Section1');
        $pathFile = sprintf($this->workingDirectory . 'cache/%s/%s%s.xml',
            $data->getEveApiSectionName(),
            $data->getEveApiName(),
            $data->getHash());
        $this->filesystem->dumpFile($pathFile, $xml);
        $event->getData()
            ->willReturn($data);
        $event->setHandledSufficiently()
            ->willReturn($event);
        $event->setData($data->setEveApiXml($xml))
            ->willReturn($event);
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent(Argument::cetera())
            ->willReturn($log);
        $messagePrefix = 'Successfully retrieved the XML of';
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent('Yapeal.Log.log',
            Logger::DEBUG,
            Argument::containingString($messagePrefix),
            Argument::cetera())
            ->willReturn($log)
            ->shouldBeCalled();
        $this->retrieveEveApi($event, 'test', $yem);
    }
    /**
     * @param Collaborator|EveApiEventInterface $event
     * @param Collaborator|LogEventInterface    $log
     * @param Collaborator|MediatorInterface    $yem
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Prophecy\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     * @throws \UnexpectedValueException
     */
    public function it_should_succeed_when_xml_has_been_cached_less_than_five_minutes_and_ignore_cached_until(
        EveApiEventInterface $event,
        LogEventInterface $log,
        MediatorInterface $yem
    ) {
        $xml = <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<eveapi version="1">
    <currentTime>2020-12-31 23:54:59</currentTime>
    <result />
    <cachedUntil>2000-12-31 23:59:59</cachedUntil>
</eveapi>
XML;
        $data = (new EveApiXmlData())->setEveApiName('Api1')
            ->setEveApiSectionName('Section1');
        $pathFile = sprintf($this->workingDirectory . 'cache/%s/%s%s.xml',
            $data->getEveApiSectionName(),
            $data->getEveApiName(),
            $data->getHash());
        $this->filesystem->dumpFile($pathFile, $xml);
        $event->getData()
            ->willReturn($data);
        $event->setHandledSufficiently()
            ->willReturn($event);
        $event->setData($data->setEveApiXml($xml))
            ->willReturn($event);
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent(Argument::cetera())
            ->willReturn($log);
        $messagePrefix = 'Successfully retrieved the XML of';
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent('Yapeal.Log.log',
            Logger::DEBUG,
            Argument::containingString($messagePrefix),
            Argument::cetera())
            ->willReturn($log)
            ->shouldBeCalled();
        $this->retrieveEveApi($event, 'test', $yem);
    }
    /**
     * @param Collaborator|EveApiEventInterface $event
     * @param Collaborator|LogEventInterface    $log
     * @param Collaborator|MediatorInterface    $yem
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Prophecy\Exception\InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function it_throws_exception_when_get_cache_dir_is_use_before_it_is_set(
        EveApiEventInterface $event,
        LogEventInterface $log,
        MediatorInterface $yem
    ) {
        $this->setCachePath('');
        $data = (new EveApiXmlData())->setEveApiName('Api1')
            ->setEveApiSectionName('Section1');
        $event->getData()
            ->willReturn($data);
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent(Argument::cetera())
            ->willReturn($log);
        $this->shouldThrow(\LogicException::class)
            ->duringRetrieveEveApi($event, 'test', $yem);
    }
    /**
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     */
    public function let()
    {
        $this->prepWorkingDirectory();
        $this->filesystem->mkdir($this->workingDirectory . 'cache');
        $this->beConstructedWith($this->workingDirectory . 'cache/');
        $this->setRetrieve(true);
    }
    /**
     *
     */
    public function letGo()
    {
        $this->removeWorkingDirectory();
    }
}
