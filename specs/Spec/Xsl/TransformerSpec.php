<?php
declare(strict_types = 1);
/**
 * Contains class TransformerSpec.
 *
 * PHP version 7.0
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Spec
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
namespace Spec\Yapeal\Xsl;

use PhpSpec\Exception\Example\FailureException;
use PhpSpec\ObjectBehavior;
use PhpSpec\Wrapper\Collaborator;
use Prophecy\Argument;
use Spec\Yapeal\FileSystemUtilTrait;
use Symfony\Component\Filesystem\Filesystem;
use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\LogEventInterface;
use Yapeal\Event\MediatorInterface;
use Yapeal\Log\Logger;
use Yapeal\Xml\EveApiXmlData;

/**
 * Class TransformerSpec
 *
 * @mixin \Yapeal\Xsl\Transformer
 *
 * @method void during($method, array $params)
 * @method void shouldBe($value)
 * @method void shouldContain($value)
 * @method void shouldNotEqual($value)
 * @method void shouldReturn($result)
 */
class TransformerSpec extends ObjectBehavior
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
        $this->shouldHaveType('Yapeal\Xsl\Transformer');
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
     * @throws \UnexpectedValueException
     */
    public function it_should_allow_result_with_no_children_elements(
        EveApiEventInterface $event,
        LogEventInterface $log,
        MediatorInterface $yem
    ) {
        $given = <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<eveapi version="1">
    <currentTime>2020-12-31 23:54:59</currentTime>
    <result>
    </result>
    <cachedUntil>2020-12-31 23:59:59</cachedUntil>
</eveapi>
XML;
        $expected = <<<'XML'
<?xml version="1.0" encoding="utf-8" standalone="no"?>
<?yapeal.parameters.json []?>
<eveapi version="1">
    <currentTime>2020-12-31 23:54:59</currentTime>
    <result />
    <cachedUntil>2020-12-31 23:59:59</cachedUntil>
</eveapi>
XML;
        $data = (new EveApiXmlData())->setEveApiName('Api1')
            ->setEveApiSectionName('Section1')
            ->setEveApiXml($given);
        $event->getData()
            ->willReturn($data);
        $event->setHandledSufficiently()
            ->shouldBeCalled();
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent(Argument::cetera())
            ->willReturn($log);
        $this->transformEveApi($event, 'test', $yem);
        if ($expected !== $data->getEveApiXml()) {
            throw new FailureException(sprintf("Expected:\n%s\n but given:\n%s", $expected, $data->getEveApiXml()));
        }
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
     * @throws \Symfony\Component\Filesystem\Exception\FileNotFoundException
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     * @throws \UnexpectedValueException
     */
    public function it_should_correctly_sorts_result_with_value_elements(
        EveApiEventInterface $event,
        LogEventInterface $log,
        MediatorInterface $yem
    ) {
        $given = <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<eveapi version="1">
    <currentTime>2020-12-31 23:54:59</currentTime>
    <result>
        <allianceID>1</allianceID>
        <allianceName>test1</allianceName>
        <gender>Female</gender>
        <characterID>2</characterID>
    </result>
    <cachedUntil>2020-12-31 23:59:59</cachedUntil>
</eveapi>
XML;
        $expected = <<<'XML'
<?xml version="1.0" encoding="utf-8" standalone="no"?>
<?yapeal.parameters.json []?>
<eveapi version="1">
    <currentTime>2020-12-31 23:54:59</currentTime>
    <result>
        <allianceID>1</allianceID>
        <allianceName>test1</allianceName>
        <characterID>2</characterID>
        <gender>Female</gender>
    </result>
    <cachedUntil>2020-12-31 23:59:59</cachedUntil>
</eveapi>
XML;
        $data = (new EveApiXmlData())->setEveApiName('Api1')
            ->setEveApiSectionName('Section1')
            ->setEveApiXml($given);
        $event->getData()
            ->willReturn($data);
        $event->setHandledSufficiently()
            ->shouldBeCalled();
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent(Argument::cetera())
            ->willReturn($log);
        $this->transformEveApi($event, 'test', $yem);
        if ($expected !== $data->getEveApiXml()) {
            throw new FailureException(sprintf("Expected:\n%s\n but given:\n%s", $expected, $data->getEveApiXml()));
        }
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
     * @throws \Symfony\Component\Filesystem\Exception\FileNotFoundException
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     * @throws \UnexpectedValueException
     */
    public function it_should_correctly_transforms_and_sort_result_with_a_rowset_element(
        EveApiEventInterface $event,
        LogEventInterface $log,
        MediatorInterface $yem
    ) {
        $this->filesystem->copy($this->projectBase . 'lib/Xsl/common.xsl', $this->workingDirectory . 'Xsl/common.xsl');
        $given = <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<eveapi version="1">
    <currentTime>2020-12-31 23:54:59</currentTime>
    <result>
        <rowset name="test" key="id" columns="id,name">
            <row id="1" name="name1"/>
            <row name="name2" id="3"/>
            <row id="2" name="name3"/>
        </rowset>
    </result>
    <cachedUntil>2020-12-31 23:59:59</cachedUntil>
</eveapi>
XML;
        $expected = <<<'XML'
<?xml version="1.0" encoding="utf-8" standalone="no"?>
<?yapeal.parameters.json []?>
<eveapi version="1">
    <currentTime>2020-12-31 23:54:59</currentTime>
    <result>
        <test key="id" columns="id,name">
            <row id="1" name="name1" />
            <row id="2" name="name3" />
            <row id="3" name="name2" />
        </test>
    </result>
    <cachedUntil>2020-12-31 23:59:59</cachedUntil>
</eveapi>
XML;
        $data = (new EveApiXmlData())->setEveApiName('Api1')
            ->setEveApiSectionName('Section1')
            ->setEveApiXml($given);
        $event->getData()
            ->willReturn($data);
        $event->setHandledSufficiently()
            ->shouldBeCalled();
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent(Argument::cetera())
            ->willReturn($log);
        $this->transformEveApi($event, 'test', $yem);
        if ($expected !== $data->getEveApiXml()) {
            throw new FailureException(sprintf("Expected:\n%s\n but given:\n%s", $expected, $data->getEveApiXml()));
        }
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
     * @throws \Symfony\Component\Filesystem\Exception\FileNotFoundException
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     * @throws \UnexpectedValueException
     */
    public function it_should_correctly_transforms_and_sort_result_with_both_value_elements_and_rowset_elements(
        EveApiEventInterface $event,
        LogEventInterface $log,
        MediatorInterface $yem
    ) {
        $given = <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<eveapi version="1">
    <currentTime>2020-12-31 23:54:59</currentTime>
    <result>
        <rowset name="test2" key="id" columns="id,name">
            <row id="6" name="name6"/>
            <row id="4" name="name4"/>
            <row name="name5" id="5"/>
        </rowset>
        <allianceID>1</allianceID>
        <allianceName>test1</allianceName>
        <gender>Female</gender>
        <characterID>2</characterID>
        <rowset name="test1" key="id" columns="id,name">
            <row id="1" name="name1"/>
            <row name="name2" id="3"/>
            <row id="2" name="name3"/>
        </rowset>
    </result>
    <cachedUntil>2020-12-31 23:59:59</cachedUntil>
</eveapi>
XML;
        $expected = <<<'XML'
<?xml version="1.0" encoding="utf-8" standalone="no"?>
<?yapeal.parameters.json []?>
<eveapi version="1">
    <currentTime>2020-12-31 23:54:59</currentTime>
    <result>
        <allianceID>1</allianceID>
        <allianceName>test1</allianceName>
        <characterID>2</characterID>
        <gender>Female</gender>
        <test1 key="id" columns="id,name">
            <row id="1" name="name1" />
            <row id="2" name="name3" />
            <row id="3" name="name2" />
        </test1>
        <test2 key="id" columns="id,name">
            <row id="4" name="name4" />
            <row id="5" name="name5" />
            <row id="6" name="name6" />
        </test2>
    </result>
    <cachedUntil>2020-12-31 23:59:59</cachedUntil>
</eveapi>
XML;
        $data = (new EveApiXmlData())->setEveApiName('Api1')
            ->setEveApiSectionName('Section1')
            ->setEveApiXml($given);
        $event->getData()
            ->willReturn($data);
        $event->setHandledSufficiently()
            ->shouldBeCalled();
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent(Argument::cetera())
            ->willReturn($log);
        $this->transformEveApi($event, 'test', $yem);
        if ($expected !== $data->getEveApiXml()) {
            throw new FailureException(sprintf("Expected:\n%s\n but given:\n%s", $expected, $data->getEveApiXml()));
        }
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
     * @throws \Symfony\Component\Filesystem\Exception\FileNotFoundException
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     * @throws \UnexpectedValueException
     */
    public function it_should_correctly_transforms_and_sort_result_with_more_than_one_rowset_element(
        EveApiEventInterface $event,
        LogEventInterface $log,
        MediatorInterface $yem
    ) {
        $given = <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<eveapi version="1">
    <currentTime>2020-12-31 23:54:59</currentTime>
    <result>
        <rowset name="test2" key="id" columns="id,name">
            <row id="6" name="name6"/>
            <row id="4" name="name4"/>
            <row name="name5" id="5"/>
        </rowset>
        <rowset name="test1" key="id" columns="id,name">
            <row id="1" name="name1"/>
            <row name="name2" id="3"/>
            <row id="2" name="name3"/>
        </rowset>
    </result>
    <cachedUntil>2020-12-31 23:59:59</cachedUntil>
</eveapi>
XML;
        $expected = <<<'XML'
<?xml version="1.0" encoding="utf-8" standalone="no"?>
<?yapeal.parameters.json []?>
<eveapi version="1">
    <currentTime>2020-12-31 23:54:59</currentTime>
    <result>
        <test1 key="id" columns="id,name">
            <row id="1" name="name1" />
            <row id="2" name="name3" />
            <row id="3" name="name2" />
        </test1>
        <test2 key="id" columns="id,name">
            <row id="4" name="name4" />
            <row id="5" name="name5" />
            <row id="6" name="name6" />
        </test2>
    </result>
    <cachedUntil>2020-12-31 23:59:59</cachedUntil>
</eveapi>
XML;
        $data = (new EveApiXmlData())->setEveApiName('Api1')
            ->setEveApiSectionName('Section1')
            ->setEveApiXml($given);
        $event->getData()
            ->willReturn($data);
        $event->setHandledSufficiently()
            ->shouldBeCalled();
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent(Argument::cetera())
            ->willReturn($log);
        $this->transformEveApi($event, 'test', $yem);
        if ($expected !== $data->getEveApiXml()) {
            throw new FailureException(sprintf("Expected:\n%s\n but given:\n%s", $expected, $data->getEveApiXml()));
        }
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
    public function it_should_log_warning_including_exception_when_given_invalid_xml(
        EveApiEventInterface $event,
        LogEventInterface $log,
        MediatorInterface $yem
    ) {
        $given = '<?xml version="1.0" encoding="utf-8"?>';
        $data = (new EveApiXmlData())->setEveApiName('Api1')
            ->setEveApiSectionName('Section1')
            ->setEveApiXml($given);
        $event->getData()
            ->willReturn($data);
        // Caching invalid XML requires these two calls
        $event->hasBeenHandled()
            ->willReturn(false);
        $event->isSufficientlyHandled()
            ->willReturn(false);
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent(Argument::cetera())
            ->willReturn($log);
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerEveApiEvent(Argument::containingString('Yapeal.Xml.Error'), $data, Argument::cetera())
            ->willReturn($event);
        $messagePrefix = 'The XML cause SimpleXMLElement exception during the transform of';
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent('Yapeal.Log.log',
            Logger::WARNING,
            Argument::containingString($messagePrefix),
            Argument::withEntry('exception', Argument::type('\Exception')))
            ->willReturn($log)
            ->shouldBeCalled();
        $this->transformEveApi($event, 'test', $yem);
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
    public function it_should_log_warning_including_exception_when_no_accessible_style_sheet_is_found(
        EveApiEventInterface $event,
        LogEventInterface $log,
        MediatorInterface $yem
    ) {
        $this->filesystem->remove($this->workingDirectory . 'Xsl/common.xsl');
        $given = <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<eveapi version="1">
    <currentTime>2020-12-31 23:54:59</currentTime>
    <result>
        <rowset name="test" key="id" columns="id,name">
            <row id="1" name="name1"/>
            <row name="name2" id="3"/>
            <row id="2" name="name3"/>
        </rowset>
    </result>
    <cachedUntil>2020-12-31 23:59:59</cachedUntil>
</eveapi>
XML;
        $data = (new EveApiXmlData())->setEveApiName('Api1')
            ->setEveApiSectionName('Section1')
            ->setEveApiXml($given);
        $event->getData()
            ->willReturn($data);
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent(Argument::cetera())
            ->willReturn($log);
        $messagePrefix = 'Failed to find accessible XSL file during the transform of';
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent('Yapeal.Log.log',
            Logger::WARNING,
            Argument::containingString($messagePrefix),
            Argument::withEntry('exception', Argument::type('\Yapeal\Exception\YapealFileSystemException')))
            ->willReturn($log)
            ->shouldBeCalled();
        $this->transformEveApi($event, 'test', $yem);
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
    public function it_should_log_warning_including_exception_when_style_sheet_contains_invalid_xml(
        EveApiEventInterface $event,
        LogEventInterface $log,
        MediatorInterface $yem
    ) {
        $this->filesystem->dumpFile($this->workingDirectory . 'Xsl/common.xsl',
            '<?xml version="1.0" encoding="utf-8"?>');
        $given = <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<eveapi version="1">
    <currentTime>2020-12-31 23:54:59</currentTime>
    <result>
        <rowset name="test" key="id" columns="id,name">
            <row id="1" name="name1"/>
            <row name="name2" id="3"/>
            <row id="2" name="name3"/>
        </rowset>
    </result>
    <cachedUntil>2020-12-31 23:59:59</cachedUntil>
</eveapi>
XML;
        $data = (new EveApiXmlData())->setEveApiName('Api1')
            ->setEveApiSectionName('Section1')
            ->setEveApiXml($given);
        $event->getData()
            ->willReturn($data);
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent(Argument::cetera())
            ->willReturn($log);
        $messagePrefix = 'SimpleXMLElement exception caused by XSL file ';
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent('Yapeal.Log.log',
            Logger::WARNING,
            Argument::containingString($messagePrefix),
            Argument::withEntry('exception', Argument::type('\Exception')))
            ->willReturn($log)
            ->shouldBeCalled();
        $this->transformEveApi($event, 'test', $yem);
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
    public function it_should_log_warning_when_given_invalid_style_sheet(
        EveApiEventInterface $event,
        LogEventInterface $log,
        MediatorInterface $yem
    ) {
        $xsl = <<<'XSL'
<xsl:transform xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <xsl:iBreakU/>
</xsl:transform>
XSL;
        $this->filesystem->dumpFile($this->workingDirectory . 'Xsl/common.xsl', $xsl);
        $given = <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<eveapi version="1">
    <currentTime>2020-12-31 23:54:59</currentTime>
    <result>
        <rowset name="test" key="id" columns="id,name">
            <row id="1" name="name1"/>
            <row name="name2" id="3"/>
            <row id="2" name="name3"/>
        </rowset>
    </result>
    <cachedUntil>2020-12-31 23:59:59</cachedUntil>
</eveapi>
XML;
        $data = (new EveApiXmlData())->setEveApiName('Api1')
            ->setEveApiSectionName('Section1')
            ->setEveApiXml($given);
        $event->getData()
            ->willReturn($data);
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent(Argument::cetera())
            ->willReturn($log);
        $messagePrefix = 'XSLT could not import style sheet during the transform of';
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent('Yapeal.Log.log',
            Logger::WARNING,
            Argument::containingString($messagePrefix),
            Argument::cetera())
            ->willReturn($log)
            ->shouldBeCalled();
        $this->transformEveApi($event, 'test', $yem);
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
    public function it_should_log_warning_when_style_sheet_is_empty(
        EveApiEventInterface $event,
        LogEventInterface $log,
        MediatorInterface $yem
    ) {
        $this->filesystem->dumpFile($this->workingDirectory . 'Xsl/common.xsl', '');
        $given = <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<eveapi version="1">
    <currentTime>2020-12-31 23:54:59</currentTime>
    <result>
        <rowset name="test" key="id" columns="id,name">
            <row id="1" name="name1"/>
            <row name="name2" id="3"/>
            <row id="2" name="name3"/>
        </rowset>
    </result>
    <cachedUntil>2020-12-31 23:59:59</cachedUntil>
</eveapi>
XML;
        $data = (new EveApiXmlData())->setEveApiName('Api1')
            ->setEveApiSectionName('Section1')
            ->setEveApiXml($given);
        $event->getData()
            ->willReturn($data);
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent(Argument::cetera())
            ->willReturn($log);
        $messagePrefix = 'Received an empty XSL file ';
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent('Yapeal.Log.log',
            Logger::WARNING,
            Argument::containingString($messagePrefix),
            Argument::cetera())
            ->willReturn($log)
            ->shouldBeCalled();
        $this->transformEveApi($event, 'test', $yem);
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
        $given = '';
        $data = (new EveApiXmlData())->setEveApiName('Api1')
            ->setEveApiSectionName('Section1')
            ->setEveApiXml($given);
        $event->getData()
            ->willReturn($data);
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent(Argument::cetera())
            ->willReturn($log);
        $messagePrefix = 'Given empty XML during the transform of';
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent('Yapeal.Log.log',
            Logger::WARNING,
            Argument::containingString($messagePrefix),
            Argument::cetera())
            ->willReturn($log)
            ->shouldBeCalled();
        $this->transformEveApi($event, 'test', $yem);
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
    public function it_should_not_try_to_do_transform_when_no_style_sheet_is_found(
        EveApiEventInterface $event,
        LogEventInterface $log,
        MediatorInterface $yem
    ) {
        $this->filesystem->remove($this->workingDirectory . 'Xsl/common.xsl');
        $given = <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<eveapi version="1">
    <currentTime>2020-12-31 23:54:59</currentTime>
    <result>
        <rowset name="test" key="id" columns="id,name">
            <row id="1" name="name1"/>
            <row name="name2" id="3"/>
            <row id="2" name="name3"/>
        </rowset>
    </result>
    <cachedUntil>2020-12-31 23:59:59</cachedUntil>
</eveapi>
XML;
        $expected = <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<?yapeal.parameters.json []?>
<eveapi version="1">
    <currentTime>2020-12-31 23:54:59</currentTime>
    <result>
        <rowset name="test" key="id" columns="id,name">
            <row id="1" name="name1" />
            <row name="name2" id="3" />
            <row id="2" name="name3" />
        </rowset>
    </result>
    <cachedUntil>2020-12-31 23:59:59</cachedUntil>
</eveapi>
XML;
        $data = (new EveApiXmlData())->setEveApiName('Api1')
            ->setEveApiSectionName('Section1')
            ->setEveApiXml($given);
        $event->getData()
            ->willReturn($data);
        $event->setHandledSufficiently()
            ->shouldNotBeCalled();
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent(Argument::cetera())
            ->willReturn($log);
        $this->transformEveApi($event, 'test', $yem);
        if ($expected !== $data->getEveApiXml()) {
            throw new FailureException(sprintf("Expected:\n%s\n but given:\n%s", $expected, $data->getEveApiXml()));
        }
    }
    /**
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     */
    public function let()
    {
        $this->prepWorkingDirectory();
        $this->filesystem->mkdir($this->workingDirectory . 'Xsl');
        $this->beConstructedWith($this->workingDirectory . 'Xsl');
        $this->filesystem->copy($this->projectBase . 'lib/Xsl/common.xsl', $this->workingDirectory . 'Xsl/common.xsl');
    }
    /**
     *
     */
    public function letGo()
    {
        $this->removeWorkingDirectory();
    }
}
