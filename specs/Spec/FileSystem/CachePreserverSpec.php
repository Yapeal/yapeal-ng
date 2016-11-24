<?php
declare(strict_types = 1);
/**
 * Contains class CachePreserverSpec.
 *
 * PHP version 7.0
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2016 Michael Cummings
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
 * @copyright 2016 Michael Cummings
 * @license   LGPL-3.0
 */
namespace Spec\Yapeal\FileSystem;

use PhpSpec\Exception\Example\FailureException;
use PhpSpec\Exception\Example\SkippingException;
use PhpSpec\ObjectBehavior;
use PhpSpec\Wrapper\Collaborator;
use Prophecy\Argument;
use Spec\Yapeal\FileSystemUtilTrait;
use Symfony\Component\Filesystem\Filesystem;
use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\LogEventInterface;
use Yapeal\Event\MediatorInterface;
use Yapeal\Xml\EveApiXmlData;

/**
 * Class CachePreserverSpec
 *
 * @mixin \Yapeal\FileSystem\CachePreserver
 *
 * @method void during($method, array $params)
 * @method void shouldBe($value)
 * @method void shouldContain($value)
 * @method void shouldNotEqual($value)
 * @method void shouldReturn($result)
 */
class CachePreserverSpec extends ObjectBehavior
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
        $this->shouldHaveType('Yapeal\FileSystem\CachePreserver');
        $this->shouldImplement('Yapeal\Event\EveApiPreserverInterface');
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
    public function it_should_actual_write_cache_file_when_successful(
        EveApiEventInterface $event,
        LogEventInterface $log,
        MediatorInterface $yem
    ) {
        $this->filesystem->mkdir($this->workingDirectory . 'cache/Section1/');
        $data = (new EveApiXmlData())->setEveApiName('Api1')
            ->setEveApiSectionName('Section1')
            ->setEveApiXml('test');
        $event->getData()
            ->willReturn($data);
        $event->setHandledSufficiently()
            ->shouldBeCalled();
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent(Argument::cetera())
            ->willReturn($log);
        $this->preserveEveApi($event, 'test', $yem);
        if (!$this->filesystem->exists(sprintf($this->workingDirectory . 'cache/%s/%s%s.xml',
            $data->getEveApiSectionName(),
            $data->getEveApiName(),
            $data->getHash()))
        ) {
            throw new FailureException('Never wrote to cache file');
        }
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
    public function it_should_do_nothing_when_should_preserve_is_false(
        EveApiEventInterface $event,
        LogEventInterface $log,
        MediatorInterface $yem
    ) {
        $this->setPreserve(false);
        $data = (new EveApiXmlData())->setEveApiName('Api1')
            ->setEveApiSectionName('Section1')
            ->setEveApiXml('test');
        $event->getData()
            ->willReturn($data);
        /** @noinspection PhpStrictTypeCheckingInspection */
        $event->setHandledSufficiently(Argument::cetera())
            ->shouldNotBeCalled();
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent(Argument::cetera())
            ->willReturn($log);
        $this->preserveEveApi($event, 'test', $yem);
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
    public function it_should_not_set_event_handled_sufficiently_when_write_fails(
        EveApiEventInterface $event,
        LogEventInterface $log,
        MediatorInterface $yem
    ) {
        throw new SkippingException('Broken test write never fails');
        $this->filesystem->mkdir($this->workingDirectory . 'cache/Section1/', 0555);
        $this->filesystem->chmod($this->workingDirectory . 'cache/', 0555);
        clearstatcache(true);
        $data = (new EveApiXmlData())->setEveApiName('Api1')
            ->setEveApiSectionName('Section1')
            ->setEveApiXml('test');
        $event->getData()
            ->willReturn($data);
        /** @noinspection PhpStrictTypeCheckingInspection */
        $event->setHandledSufficiently(Argument::cetera())
            ->shouldNotBeCalled();
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent(Argument::cetera())
            ->willReturn($log);
        $this->preserveEveApi($event, 'test', $yem);
        $this->filesystem->chmod($this->workingDirectory . 'cache/', 0777);
        $this->filesystem->chmod($this->workingDirectory . 'cache/Section1/', 0777);
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
    public function it_should_not_set_event_handled_sufficiently_when_xml_is_empty(
        EveApiEventInterface $event,
        LogEventInterface $log,
        MediatorInterface $yem
    ) {
        $data = (new EveApiXmlData())->setEveApiName('Api1')
            ->setEveApiSectionName('Section1')
            ->setEveApiXml('');
        $event->getData()
            ->willReturn($data);
        /** @noinspection PhpStrictTypeCheckingInspection */
        $event->setHandledSufficiently(Argument::cetera())
            ->shouldNotBeCalled();
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent(Argument::cetera())
            ->willReturn($log);
        $this->preserveEveApi($event, 'test', $yem);
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
    public function it_should_not_write_cache_file_when_xml_is_empty(
        EveApiEventInterface $event,
        LogEventInterface $log,
        MediatorInterface $yem
    ) {
        $data = (new EveApiXmlData())->setEveApiName('Api1')
            ->setEveApiSectionName('Section1')
            ->setEveApiXml('');
        $event->getData()
            ->willReturn($data);
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent(Argument::cetera())
            ->willReturn($log);
        $this->preserveEveApi($event, 'test', $yem);
        if ($this->filesystem->exists(sprintf($this->workingDirectory . 'cache/%s/%s%s.xml',
            $data->getEveApiSectionName(),
            $data->getEveApiName(),
            $data->getHash()))
        ) {
            throw new FailureException('Wrote to cache file');
        }
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
    public function it_should_set_event_handled_sufficiently_when_write_succeeds(
        EveApiEventInterface $event,
        LogEventInterface $log,
        MediatorInterface $yem
    ) {
        $this->filesystem->mkdir($this->workingDirectory . 'cache/Section1/');
        $data = (new EveApiXmlData())->setEveApiName('Api1')
            ->setEveApiSectionName('Section1')
            ->setEveApiXml('test');
        $event->getData()
            ->willReturn($data);
        $event->setHandledSufficiently()
            ->shouldBeCalled();
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent(Argument::cetera())
            ->willReturn($log);
        $this->preserveEveApi($event, 'test', $yem);
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
            ->setEveApiSectionName('Section1')
            ->setEveApiXml('test');
        $event->getData()
            ->willReturn($data);
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent(Argument::cetera())
            ->willReturn($log);
        $mess = 'Trying to use cachePath before it was set';
        $this->shouldThrow(new \LogicException($mess))
            ->during('preserveEveApi', [$event, 'test', $yem]);
    }
    /**
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     */
    public function let()
    {
        $this->prepWorkingDirectory();
        $this->filesystem->mkdir($this->workingDirectory . 'cache');
        $this->beConstructedWith($this->workingDirectory . 'cache/');
        $this->setPreserve(true);
    }
    /**
     *
     */
    public function letGo()
    {
        $this->removeWorkingDirectory();
    }
}
