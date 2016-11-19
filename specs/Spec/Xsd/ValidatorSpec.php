<?php
declare(strict_types = 1);
/**
 * Contains class ValidatorSpec.
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
namespace Spec\Yapeal\Xsd;

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
 * Class ValidatorSpec
 *
 * @mixin \Yapeal\Xsd\Validator
 *
 * @method void during($method, array $params)
 * @method void shouldBe($value)
 * @method void shouldContain($value)
 * @method void shouldNotEqual($value)
 * @method void shouldReturn($result)
 */
class ValidatorSpec extends ObjectBehavior
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
        $this->shouldHaveType('Yapeal\Xsd\Validator');
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
    public function it_should_fail_validation_when_cached_until_is_less_than_or_equal_to_current_time(
        EveApiEventInterface $event,
        LogEventInterface $log,
        MediatorInterface $yem
    ) {
        $xsd = /** @lang XML */
            <<<'XSD'
<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"
    elementFormDefault="qualified" xml:lang="en-US">
    <xs:element name="eveapi">
        <xs:complexType>
            <xs:sequence>
                <xs:element type="eveNEDTType" name="currentTime"/>
                <xs:choice>
                    <xs:element type="xs:string" name="error"/>
                    <xs:element type="xs:anyType" name="result"/>
                </xs:choice>
                <xs:element type="eveNEDTType" name="cachedUntil"/>
            </xs:sequence>
            <xs:attribute type="xs:unsignedByte" name="version"/>
        </xs:complexType>
    </xs:element>
    <xs:simpleType name="eveNEDTType">
        <xs:annotation>
            <xs:documentation>Date/time that can NOT be empty.
            </xs:documentation>
        </xs:annotation>
        <xs:restriction base="xs:string">
            <xs:pattern
                value="(\d{4})-((0[13578])|10|12)-((0[1-9])|([1-2]\d)|30|31)(T|\s)(([01]\d)|(2[0-3])):([0-5]\d):([0-5]\d)"/>
            <xs:pattern
                value="(\d{4})-((0[469])|11)-((0[1-9])|([1-2]\d)|30)(T|\s)(([01]\d)|(2[0-3])):([0-5]\d):([0-5]\d)"/>
            <xs:pattern
                value="(\d{4})-02-((0[1-9])|([1-2]\d))(T|\s)(([01]\d)|(2[0-3])):([0-5]\d):([0-5]\d)"/>
        </xs:restriction>
    </xs:simpleType>
</xs:schema>
XSD;
        $this->filesystem->dumpFile($this->workingDirectory . 'Xsd/common.xsd', $xsd);
        $given = /** @lang XML */
            <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<?yapeal.parameters.json []?>
<eveapi version="1">
    <currentTime>2020-12-31 23:54:59</currentTime>
    <result />
    <cachedUntil>2020-12-31 23:54:59</cachedUntil>
</eveapi>
XML;
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
        $messagePrefix = 'CachedUntil is invalid was given ';
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent('Yapeal.Log.log',
            Logger::WARNING,
            Argument::containingString($messagePrefix),
            Argument::cetera())
            ->willReturn($log)
            ->shouldBeCalled();
        $this->validateEveApi($event, 'test', $yem);
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
    public function it_should_fail_validation_when_the_diff_between_cached_until_and_current_time_is_greater_than_a_day(
        EveApiEventInterface $event,
        LogEventInterface $log,
        MediatorInterface $yem
    ) {
        $xsd = /** @lang XML */
            <<<'XSD'
<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"
    elementFormDefault="qualified" xml:lang="en-US">
    <xs:element name="eveapi">
        <xs:complexType>
            <xs:sequence>
                <xs:element type="eveNEDTType" name="currentTime"/>
                <xs:choice>
                    <xs:element type="xs:string" name="error"/>
                    <xs:element type="xs:anyType" name="result"/>
                </xs:choice>
                <xs:element type="eveNEDTType" name="cachedUntil"/>
            </xs:sequence>
            <xs:attribute type="xs:unsignedByte" name="version"/>
        </xs:complexType>
    </xs:element>
    <xs:simpleType name="eveNEDTType">
        <xs:annotation>
            <xs:documentation>Date/time that can NOT be empty.
            </xs:documentation>
        </xs:annotation>
        <xs:restriction base="xs:string">
            <xs:pattern
                value="(\d{4})-((0[13578])|10|12)-((0[1-9])|([1-2]\d)|30|31)(T|\s)(([01]\d)|(2[0-3])):([0-5]\d):([0-5]\d)"/>
            <xs:pattern
                value="(\d{4})-((0[469])|11)-((0[1-9])|([1-2]\d)|30)(T|\s)(([01]\d)|(2[0-3])):([0-5]\d):([0-5]\d)"/>
            <xs:pattern
                value="(\d{4})-02-((0[1-9])|([1-2]\d))(T|\s)(([01]\d)|(2[0-3])):([0-5]\d):([0-5]\d)"/>
        </xs:restriction>
    </xs:simpleType>
</xs:schema>
XSD;
        $this->filesystem->dumpFile($this->workingDirectory . 'Xsd/common.xsd', $xsd);
        $given = /** @lang XML */
            <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<?yapeal.parameters.json []?>
<eveapi version="1">
    <currentTime>2020-12-29 23:54:59</currentTime>
    <result />
    <cachedUntil>2020-12-31 23:54:59</cachedUntil>
</eveapi>
XML;
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
        $messagePrefix = 'CachedUntil is excessively long was given ';
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent('Yapeal.Log.log',
            Logger::WARNING,
            Argument::containingString($messagePrefix),
            Argument::cetera())
            ->willReturn($log)
            ->shouldBeCalled();
        $this->validateEveApi($event, 'test', $yem);
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
    public function it_should_log_a_warning_including_exception_when_no_accessible_schema_is_found(
        EveApiEventInterface $event,
        LogEventInterface $log,
        MediatorInterface $yem
    ) {
        $this->filesystem->remove($this->workingDirectory . 'Xsd/common.xsd');
        $given = <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<?yapeal.parameters.json []?>
<eveapi version="1">
    <currentTime>2020-12-31 23:54:59</currentTime>
    <result>
        <accounts key="accountID" columns="accountID,balance">
            <row accountID="1" balance="0.01" />
            <row accountID="2" balance="0.03" />
            <row accountID="3" balance="0.02" />
        </accounts>
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
        $messagePrefix = 'Failed to find accessible XSD file during the validation of';
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent('Yapeal.Log.log',
            Logger::WARNING,
            Argument::containingString($messagePrefix),
            Argument::withEntry('exception', Argument::type('\Yapeal\Exception\YapealFileSystemException')))
            ->willReturn($log)
            ->shouldBeCalled();
        $this->validateEveApi($event, 'test', $yem);
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
    public function it_should_log_a_warning_including_exception_when_the_schema_contains_invalid_xml(
        EveApiEventInterface $event,
        LogEventInterface $log,
        MediatorInterface $yem
    ) {
        $this->filesystem->dumpFile($this->workingDirectory . 'Xsd/common.xsd',
            '<?xml version="1.0" encoding="utf-8"?>');
        $given = <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<?yapeal.parameters.json []?>
<eveapi version="1">
    <currentTime>2020-12-31 23:54:59</currentTime>
    <result>
        <accounts key="accountID" columns="accountID,balance">
            <row accountID="1" balance="0.01" />
            <row accountID="2" balance="0.03" />
            <row accountID="3" balance="0.02" />
        </accounts>
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
        $messagePrefix = 'SimpleXMLElement exception caused by XSD file ';
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent('Yapeal.Log.log',
            Logger::WARNING,
            Argument::containingString($messagePrefix),
            Argument::withEntry('exception', Argument::type('\Exception')))
            ->willReturn($log)
            ->shouldBeCalled();
        $this->validateEveApi($event, 'test', $yem);
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
    public function it_should_log_a_warning_when_given_html_instead_of_xml(
        EveApiEventInterface $event,
        LogEventInterface $log,
        MediatorInterface $yem
    ) {
        $xsd = /** @lang XML */
            <<<'XSD'
<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"
    elementFormDefault="qualified" xml:lang="en-US">
    <xs:element name="eveapi">
        <xs:complexType>
            <xs:sequence>
                <xs:element type="xs:string" name="currentTime"/>
                <xs:choice>
                    <xs:element type="xs:string" name="error"/>
                    <xs:element type="xs:simpleType" name="result"/>
                </xs:choice>
                <xs:element type="xs:string" name="cachedUntil"/>
            </xs:sequence>
            <xs:attribute type="xs:unsignedByte" name="version"/>
        </xs:complexType>
    </xs:element>
</xs:schema>
XSD;
        $this->filesystem->dumpFile($this->workingDirectory . 'Xsd/common.xsd', $xsd);
        $given = /** @lang HTML */
            <<<'XML'
<!DOCTYPE html>
<html>
    <head />
    <body />
</html>
XML;
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
        $messagePrefix = 'Received HTML error doc instead of XML data during the validation of';
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent('Yapeal.Log.log',
            Logger::WARNING,
            Argument::containingString($messagePrefix),
            Argument::cetera())
            ->willReturn($log)
            ->shouldBeCalled();
        $this->validateEveApi($event, 'test', $yem);
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
    public function it_should_log_a_warning_when_given_invalid_xml(
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
        $messagePrefix = 'DOM could not load XML during the validation of';
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent('Yapeal.Log.log',
            Logger::WARNING,
            Argument::containingString($messagePrefix),
            Argument::cetera())
            ->willReturn($log)
            ->shouldBeCalled();
        $this->validateEveApi($event, 'test', $yem);
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
    public function it_should_log_a_warning_when_the_schema_is_empty(
        EveApiEventInterface $event,
        LogEventInterface $log,
        MediatorInterface $yem
    ) {
        $this->filesystem->dumpFile($this->workingDirectory . 'Xsd/common.xsd', '');
        $given = <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<?yapeal.parameters.json []?>
<eveapi version="1">
    <currentTime>2020-12-31 23:54:59</currentTime>
    <result>
        <accounts key="accountID" columns="accountID,balance">
            <row accountID="1" balance="0.01" />
            <row accountID="2" balance="0.03" />
            <row accountID="3" balance="0.02" />
        </accounts>
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
        $messagePrefix = 'Received an empty XSD file ';
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent('Yapeal.Log.log',
            Logger::WARNING,
            Argument::containingString($messagePrefix),
            Argument::cetera())
            ->willReturn($log)
            ->shouldBeCalled();
        $this->validateEveApi($event, 'test', $yem);
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
    public function it_should_log_a_warning_when_the_xml_fails_validation(
        EveApiEventInterface $event,
        LogEventInterface $log,
        MediatorInterface $yem
    ) {
        $xsd = /** @lang XML */
            <<<'XSD'
<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"
    elementFormDefault="qualified" xml:lang="en-US">
    <xs:element name="eveapi">
        <xs:complexType>
            <xs:sequence>
                <xs:element type="xs:string" name="currentTime"/>
                <xs:choice>
                    <xs:element type="xs:string" name="error"/>
                    <xs:element type="xs:simpleType" name="result"/>
                </xs:choice>
                <xs:element type="xs:string" name="cachedUntil"/>
            </xs:sequence>
            <xs:attribute type="xs:unsignedByte" name="version"/>
        </xs:complexType>
    </xs:element>
</xs:schema>
XSD;
        $this->filesystem->dumpFile($this->workingDirectory . 'Xsd/common.xsd', $xsd);
        $given = /** @lang XML */
            <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<?yapeal.parameters.json []?>
<eveapi version="1">
    <currentTime>2020-12-31 23:54:59</currentTime>
    <result>
        <accounts key="accountID" columns="accountID,balance">
            <row accountID="1" balance="0.01" />
            <row accountID="2" balance="0.03" />
            <row accountID="3" balance="0.02" />
        </accounts>
    </result>
    <cachedUntil>2020-12-31 23:59:59</cachedUntil>
</eveapi>
XML;
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
        $messagePrefix = 'DOM schema could not validate XML during the validation of';
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent('Yapeal.Log.log',
            Logger::WARNING,
            Argument::containingString($messagePrefix),
            Argument::cetera())
            ->willReturn($log)
            ->shouldBeCalled();
        $this->validateEveApi($event, 'test', $yem);
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
    public function it_should_log_a_warning_when_the_xml_is_empty(
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
        $messagePrefix = 'Given empty XML during the validation of';
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent('Yapeal.Log.log',
            Logger::WARNING,
            Argument::containingString($messagePrefix),
            Argument::cetera())
            ->willReturn($log)
            ->shouldBeCalled();
        $this->validateEveApi($event, 'test', $yem);
    }
    /**
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     */
    public function let()
    {
        $this->prepWorkingDirectory();
        $this->filesystem->mkdir($this->workingDirectory . 'Xsd');
        $this->beConstructedWith($this->workingDirectory . 'Xsd');
        $this->filesystem->copy($this->projectBase . 'lib/Xsd/common.xsd', $this->workingDirectory . 'Xsd/common.xsd');
    }
    /**
     *
     */
    public function letGo()
    {
        $this->removeWorkingDirectory();
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
    public function it_should_secede_when_the_xml_result_and_schema_agree(
        EveApiEventInterface $event,
        LogEventInterface $log,
        MediatorInterface $yem)    {
        $xsd = /** @lang XML */
            <<<'XSD'
<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"
    elementFormDefault="qualified" xml:lang="en-US">
    <xs:element name="eveapi">
        <xs:complexType>
            <xs:sequence>
                <xs:element type="xs:string" name="currentTime"/>
                <xs:choice>
                    <xs:element type="xs:string" name="error"/>
                    <xs:element type="xs:anyType" name="result"/>
                </xs:choice>
                <xs:element type="xs:string" name="cachedUntil"/>
            </xs:sequence>
            <xs:attribute type="xs:unsignedByte" name="version"/>
        </xs:complexType>
    </xs:element>
</xs:schema>
XSD;
        $this->filesystem->dumpFile($this->workingDirectory . 'Xsd/common.xsd', $xsd);
        $given = /** @lang XML */
            <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
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
        $event->setHandledSufficiently()->willReturn($event);
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent(Argument::cetera())
            ->willReturn($log);
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerEveApiEvent(Argument::containingString('Yapeal.Xml.Error'), $data, Argument::cetera())
            ->willReturn($event);
        $messagePrefix = 'Successfully validated the XML during the validation of';
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent('Yapeal.Log.log',
            Logger::INFO,
            Argument::containingString($messagePrefix),
            Argument::cetera())
            ->willReturn($log)
            ->shouldBeCalled();
        $this->validateEveApi($event, 'test', $yem);
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
    public function it_should_secede_when_given_xml_error_element_and_a_schema_that_allows_it(
        EveApiEventInterface $event,
        LogEventInterface $log,
        MediatorInterface $yem
    ) {
        $xsd = /** @lang XML */
            <<<'XSD'
<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"
    elementFormDefault="qualified" xml:lang="en-US">
    <xs:element name="eveapi">
        <xs:complexType>
            <xs:sequence>
                <xs:element type="xs:string" name="currentTime"/>
                <xs:choice>
                    <xs:element type="xs:string" name="error"/>
                    <xs:element type="xs:anyType" name="result"/>
                </xs:choice>
                <xs:element type="xs:string" name="cachedUntil"/>
            </xs:sequence>
            <xs:attribute type="xs:unsignedByte" name="version"/>
        </xs:complexType>
    </xs:element>
</xs:schema>
XSD;
        $this->filesystem->dumpFile($this->workingDirectory . 'Xsd/common.xsd', $xsd);
        $given = /** @lang XML */
            <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<?yapeal.parameters.json []?>
<eveapi version="1">
    <currentTime>2020-12-31 23:54:59</currentTime>
    <error />
    <cachedUntil>2020-12-31 23:59:59</cachedUntil>
</eveapi>
XML;
        $data = (new EveApiXmlData())->setEveApiName('Api1')
            ->setEveApiSectionName('Section1')
            ->setEveApiXml($given);
        $event->getData()
            ->willReturn($data);
        // Processing error XML requires these two calls
        $event->hasBeenHandled()
            ->willReturn(false);
        $event->isSufficientlyHandled()
            ->willReturn(false);
        $event->setHandledSufficiently()
            ->willReturn($event);
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent(Argument::cetera())
            ->willReturn($log);
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerEveApiEvent(Argument::containingString('Yapeal.Xml.Error'), $data, Argument::cetera())
            ->willReturn($event);
        $messagePrefix = 'Successfully validated the XML during the validation of';
        /** @noinspection PhpStrictTypeCheckingInspection */
        $yem->triggerLogEvent('Yapeal.Log.log',
            Logger::INFO,
            Argument::containingString($messagePrefix),
            Argument::cetera())
            ->willReturn($log)
            ->shouldBeCalled();
        $this->validateEveApi($event, 'test', $yem);
    }
}
