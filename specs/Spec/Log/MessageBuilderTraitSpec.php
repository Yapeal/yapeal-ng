<?php
declare(strict_types = 1);
/**
 * Contains class MessageBuilderTraitSpec.
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
namespace Spec\Yapeal\Log;

use PhpSpec\ObjectBehavior;
use PhpSpec\Wrapper\Collaborator;
use Prophecy\Argument;
use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Class MessageBuilderTraitSpec
 *
 * @mixin \Spec\Yapeal\Log\MockMessageCaller
 * @mixin \Yapeal\Log\MessageBuilderTrait
 *
 * @method void during($method, array $params)
 * @method void shouldBe($value)
 * @method void shouldContain($value)
 * @method void shouldNotEqual($value)
 * @method void shouldReturn($result)
 */
class MessageBuilderTraitSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldImplement(MockMessageCaller::class);
    }
    /**
     * @param Collaborator|EveApiReadWriteInterface $data
     *
     * @throws \DomainException
     * @throws \LogicException
     * @throws \Prophecy\Exception\InvalidArgumentException
     */
    public function it_should_use_given_character_id_in_create_eve_api_message(EveApiReadWriteInterface $data)
    {
        $data->getEveApiSectionName()
            ->willReturn('Bogus');
        $data->getEveApiName()
            ->willReturn('Api');
        $data->hasEveApiArgument('keyID')
            ->willReturn(true);
        $data->getEveApiArgument('keyID')
            ->willReturn('123');
        $data->hasEveApiArgument('characterID')
            ->willReturn(true);
        $data->getEveApiArgument('characterID')
            ->willReturn('456');
        /** @noinspection PhpStrictTypeCheckingInspection */
        $data->hasEveApiArgument(Argument::type('string'))
            ->willReturn(false);
        $this->createEveApiMessage('test prefix', $data)
            ->shouldReturn('test prefix Eve API bogus/Api for keyID = 123 and characterID = 456');
    }
    /**
     * @param Collaborator|EveApiReadWriteInterface $data
     *
     * @throws \DomainException
     * @throws \LogicException
     * @throws \Prophecy\Exception\InvalidArgumentException
     */
    public function it_should_use_given_corporation_id_in_create_eve_api_message(EveApiReadWriteInterface $data)
    {
        $data->getEveApiSectionName()
            ->willReturn('Bogus');
        $data->getEveApiName()
            ->willReturn('Api');
        $data->hasEveApiArgument('keyID')
            ->willReturn(true);
        $data->getEveApiArgument('keyID')
            ->willReturn('123');
        $data->hasEveApiArgument('corporationID')
            ->willReturn(true);
        $data->getEveApiArgument('corporationID')
            ->willReturn('456');
        /** @noinspection PhpStrictTypeCheckingInspection */
        $data->hasEveApiArgument(Argument::type('string'))
            ->willReturn(false);
        $this->createEveApiMessage('test prefix', $data)
            ->shouldReturn('test prefix Eve API bogus/Api for keyID = 123 and corporationID = 456');
    }
    /**
     * @param Collaborator|EveApiReadWriteInterface $data
     *
     * @throws \DomainException
     * @throws \LogicException
     * @throws \Prophecy\Exception\InvalidArgumentException
     */
    public function it_should_use_given_event_name_in_create_event_message(EveApiReadWriteInterface $data)
    {
        $data->getEveApiSectionName()
            ->willReturn('Bogus');
        $data->getEveApiName()
            ->willReturn('Api');
        $data->hasEveApiArgument('keyID')
            ->willReturn(true);
        $data->getEveApiArgument('keyID')
            ->willReturn('123');
        /** @noinspection PhpStrictTypeCheckingInspection */
        $data->hasEveApiArgument(Argument::type('string'))
            ->willReturn(false);
        $this->createEventMessage('test prefix', $data, 'Public.Test')
            ->shouldReturn('test prefix the Public.Test event while processing Eve API bogus/Api for keyID = 123');
    }
    /**
     * @param Collaborator|EveApiReadWriteInterface $data
     *
     * @throws \DomainException
     * @throws \LogicException
     * @throws \Prophecy\Exception\InvalidArgumentException
     */
    public function it_should_use_given_event_name_in_get_emitting_event_message(EveApiReadWriteInterface $data)
    {
        $data->getEveApiSectionName()
            ->willReturn('Bogus');
        $data->getEveApiName()
            ->willReturn('Api');
        $data->hasEveApiArgument('keyID')
            ->willReturn(true);
        $data->getEveApiArgument('keyID')
            ->willReturn('123');
        /** @noinspection PhpStrictTypeCheckingInspection */
        $data->hasEveApiArgument(Argument::type('string'))
            ->willReturn(false);
        $this->getEmittingEventMessage($data, 'Public.Test')
            ->shouldReturn('Emitting the Public.Test event while processing Eve API bogus/Api for keyID = 123');
    }
    /**
     * @param Collaborator|EveApiReadWriteInterface $data
     *
     * @throws \DomainException
     * @throws \LogicException
     * @throws \Prophecy\Exception\InvalidArgumentException
     */
    public function it_should_use_given_event_name_in_get_empty_xml_data_message(EveApiReadWriteInterface $data)
    {
        $data->getEveApiSectionName()
            ->willReturn('Bogus');
        $data->getEveApiName()
            ->willReturn('Api');
        $data->hasEveApiArgument('keyID')
            ->willReturn(true);
        $data->getEveApiArgument('keyID')
            ->willReturn('123');
        /** @noinspection PhpStrictTypeCheckingInspection */
        $data->hasEveApiArgument(Argument::type('string'))
            ->willReturn(false);
        $this->getEmptyXmlDataMessage($data, 'Public.Test')
            ->shouldReturn('XML is empty after the Public.Test event while processing Eve API bogus/Api for keyID = 123');
    }
    /**
     * @param Collaborator|EveApiReadWriteInterface $data
     *
     * @throws \DomainException
     * @throws \LogicException
     * @throws \Prophecy\Exception\InvalidArgumentException
     */
    public function it_should_use_given_event_name_in_get_finished_event_message(EveApiReadWriteInterface $data)
    {
        $data->getEveApiSectionName()
            ->willReturn('Bogus');
        $data->getEveApiName()
            ->willReturn('Api');
        $data->hasEveApiArgument('keyID')
            ->willReturn(true);
        $data->getEveApiArgument('keyID')
            ->willReturn('123');
        /** @noinspection PhpStrictTypeCheckingInspection */
        $data->hasEveApiArgument(Argument::type('string'))
            ->willReturn(false);
        $this->getFinishedEventMessage($data, 'Public.Test')
            ->shouldReturn('Finished the Public.Test event while processing Eve API bogus/Api for keyID = 123');
    }
    /**
     * @param Collaborator|EveApiReadWriteInterface $data
     *
     * @throws \DomainException
     * @throws \LogicException
     * @throws \Prophecy\Exception\InvalidArgumentException
     */
    public function it_should_use_given_event_name_in_get_non_handled_event_message(EveApiReadWriteInterface $data)
    {
        $data->getEveApiSectionName()
            ->willReturn('Bogus');
        $data->getEveApiName()
            ->willReturn('Api');
        $data->hasEveApiArgument('keyID')
            ->willReturn(true);
        $data->getEveApiArgument('keyID')
            ->willReturn('123');
        /** @noinspection PhpStrictTypeCheckingInspection */
        $data->hasEveApiArgument(Argument::type('string'))
            ->willReturn(false);
        $this->getNonHandledEventMessage($data, 'Public.Test')
            ->shouldReturn('Nothing reported handling the Public.Test event while processing Eve API bogus/Api for keyID = 123');
    }
    /**
     * @param Collaborator|EveApiReadWriteInterface $data
     *
     * @throws \DomainException
     * @throws \LogicException
     * @throws \Prophecy\Exception\InvalidArgumentException
     */
    public function it_should_use_given_event_name_in_get_sufficiently_handled_event_message(
        EveApiReadWriteInterface $data
    ) {
        $data->getEveApiSectionName()
            ->willReturn('Bogus');
        $data->getEveApiName()
            ->willReturn('Api');
        $data->hasEveApiArgument('keyID')
            ->willReturn(true);
        $data->getEveApiArgument('keyID')
            ->willReturn('123');
        /** @noinspection PhpStrictTypeCheckingInspection */
        $data->hasEveApiArgument(Argument::type('string'))
            ->willReturn(false);
        $this->getSufficientlyHandledEventMessage($data, 'Public.Test')
            ->shouldReturn('Sufficiently handled the Public.Test event while processing Eve API bogus/Api for keyID = 123');
    }
    /**
     * @param Collaborator|EveApiReadWriteInterface $data
     *
     * @throws \DomainException
     * @throws \LogicException
     * @throws \Prophecy\Exception\InvalidArgumentException
     */
    public function it_should_use_given_event_name_in_get_was_handled_event_message(EveApiReadWriteInterface $data)
    {
        $data->getEveApiSectionName()
            ->willReturn('Bogus');
        $data->getEveApiName()
            ->willReturn('Api');
        $data->hasEveApiArgument('keyID')
            ->willReturn(true);
        $data->getEveApiArgument('keyID')
            ->willReturn('123');
        /** @noinspection PhpStrictTypeCheckingInspection */
        $data->hasEveApiArgument(Argument::type('string'))
            ->willReturn(false);
        $this->getWasHandledEventMessage($data, 'Public.Test')
            ->shouldReturn('Handled the Public.Test event while processing Eve API bogus/Api for keyID = 123');
    }
    /**
     * @param Collaborator|EveApiReadWriteInterface $data
     *
     * @throws \DomainException
     * @throws \LogicException
     * @throws \Prophecy\Exception\InvalidArgumentException
     */
    public function it_should_use_given_file_name_in_get_failed_to_write_file_message(EveApiReadWriteInterface $data)
    {
        $data->getEveApiSectionName()
            ->willReturn('Bogus');
        $data->getEveApiName()
            ->willReturn('Api');
        $data->hasEveApiArgument('keyID')
            ->willReturn(true);
        $data->getEveApiArgument('keyID')
            ->willReturn('123');
        /** @noinspection PhpStrictTypeCheckingInspection */
        $data->hasEveApiArgument(Argument::type('string'))
            ->willReturn(false);
        $this->getFailedToWriteFileMessage($data, 'Cache/BogusApi')
            ->shouldReturn('Writing file Cache/BogusApi failed during the creation of Eve API bogus/Api for keyID = 123');
    }
    /**
     * @param Collaborator|EveApiReadWriteInterface $data
     *
     * @throws \DomainException
     * @throws \LogicException
     * @throws \Prophecy\Exception\InvalidArgumentException
     */
    public function it_should_use_given_key_id_in_create_eve_api_message(EveApiReadWriteInterface $data)
    {
        $data->getEveApiSectionName()
            ->willReturn('Bogus');
        $data->getEveApiName()
            ->willReturn('Api');
        $data->hasEveApiArgument('keyID')
            ->willReturn(true);
        $data->getEveApiArgument('keyID')
            ->willReturn('123');
        /** @noinspection PhpStrictTypeCheckingInspection */
        $data->hasEveApiArgument(Argument::type('string'))
            ->willReturn(false);
        $this->createEveApiMessage('test prefix', $data)
            ->shouldReturn('test prefix Eve API bogus/Api for keyID = 123');
    }
    /**
     * @param Collaborator|EveApiReadWriteInterface $data
     *
     * @throws \DomainException
     * @throws \LogicException
     * @throws \Prophecy\Exception\InvalidArgumentException
     */
    public function it_should_use_given_parameters_in_get_received_event_message(EveApiReadWriteInterface $data)
    {
        $data->getEveApiSectionName()
            ->willReturn('Bogus');
        $data->getEveApiName()
            ->willReturn('Api');
        $data->hasEveApiArgument('keyID')
            ->willReturn(true);
        $data->getEveApiArgument('keyID')
            ->willReturn('123');
        /** @noinspection PhpStrictTypeCheckingInspection */
        $data->hasEveApiArgument(Argument::type('string'))
            ->willReturn(false);
        $this->getReceivedEventMessage($data, 'Public.Test', __CLASS__)
            ->shouldReturn('Received in '
                . __CLASS__
                . ' the Public.Test event while processing Eve API bogus/Api for keyID = 123');
    }
    public function let()
    {
        $this->beAnInstanceOf(MockMessageCaller::class);
    }
}
