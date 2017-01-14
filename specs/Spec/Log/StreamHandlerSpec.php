<?php
declare(strict_types = 1);
/**
 * Contains class StreamHandlerSpec.
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

use Monolog\Formatter\FormatterInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Webmozart\Assert\Assert;
use Yapeal\Log\Logger;
use Yapeal\Log\StreamHandler;

/**
 * Class StreamHandlerSpec
 *
 * @mixin \Yapeal\Log\StreamHandler
 *
 * @method void during($method, array $params)
 * @method void shouldBe($value)
 * @method void shouldContain($value)
 * @method void shouldNotEqual($value)
 * @method void shouldReturn($result)
 */
class StreamHandlerSpec extends ObjectBehavior
{
    use TestCaseTrait;
    /**
     * @param FormatterInterface|\PhpSpec\Wrapper\Collaborator $formatter
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Prophecy\Exception\InvalidArgumentException
     * @throws \Psr\Log\InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function it_can_handle_record(FormatterInterface $formatter)
    {
        /** @noinspection PhpParamsInspection */
        $formatter->format(Argument::type('array'))
            ->will(function ($args) {
                return $args[0]['message'];
            });
        $handle = fopen('php://memory', 'ab+');
        $this->beConstructedWith($handle);
        $this->setFormatter($formatter);
        $this->handle($this->getRecord(Logger::WARNING, 'test'));
        $this->handle($this->getRecord(Logger::WARNING, 'test2'));
        $this->handle($this->getRecord(Logger::WARNING, 'test3'));
        fseek($handle, 0);
        Assert::same(fread($handle, 100), 'testtest2test3');
        fclose($handle);
    }
    /**
     * @param FormatterInterface|\PhpSpec\Wrapper\Collaborator $formatter
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Prophecy\Exception\InvalidArgumentException
     * @throws \Psr\Log\InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function it_can_handle_record_batches(FormatterInterface $formatter)
    {
        /** @noinspection PhpParamsInspection */
        $formatter->format(Argument::type('array'))
            ->will(function ($args) {
                return $args[0]['message'];
            });
        $handle = fopen('php://memory', 'ab+');
        $this->beConstructedWith($handle);
        $this->setFormatter($formatter);
        $this->handleBatch($this->getMultipleRecords());
        fseek($handle, 0);
        Assert::same(fread($handle, 100), 'debug message 1debug message 2informationwarningerror');
        fclose($handle);
    }
    public function it_is_initializable()
    {
        $this->shouldHaveType(StreamHandler::class);
    }
    public function it_leaves_external_stream_open_after_writing_record_in_close()
    {
        $handle = fopen('php://memory', 'ab+');
        $this->beConstructedWith($handle);
        Assert::true(is_resource($handle));
        $this->getStream()
            ->shouldBeResource();
        $this->close();
        Assert::true(is_resource($handle));
        $this->getStream()
            ->shouldBeNull();
        fclose($handle);
    }
    /**
     * @param FormatterInterface|\PhpSpec\Wrapper\Collaborator $formatter
     *
     * @throws \LogicException
     * @throws \Prophecy\Exception\InvalidArgumentException
     * @throws \Psr\Log\InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function it_obeys_set_level_in_handle(FormatterInterface $formatter)
    {
        /** @noinspection PhpParamsInspection */
        $formatter->format(Argument::type('array'))
            ->will(function ($args) {
                return $args[0]['message'];
            });
        $handle = fopen('php://memory', 'ab+');
        $args = [$handle, Logger::DEBUG, false];
        $this->beConstructedWith(...$args);
        $this->setFormatter($formatter);
        $this->handle($this->getRecord(Logger::WARNING, 'test'));
        $this->setLevel(Logger::WARNING);
        $this->handle($this->getRecord(Logger::DEBUG, 'test'));
        fseek($handle, 0);
        Assert::same(fread($handle, 100), 'test');
        fclose($handle);
    }
    public function it_releases_internal_stream_after_writing_record_in_close()
    {
        $this->getStream()
            ->shouldBeNull();
        $this->handle($this->getRecord(Logger::WARNING, 'test'));
        $this->getStream()
            ->shouldBeResource();
        $this->close();
        $this->getStream()
            ->shouldBeNull();
    }
    public function it_returns_false_from_is_handling_when_preserve_is_false()
    {
        $this->isHandling($this->getRecord())
            ->shouldReturn(true);
        $this->setPreserve(false);
        $this->isHandling($this->getRecord())
            ->shouldReturn(false);
    }
    public function it_sets_file_permissions_for_local_stream_correctly()
    {
        // First normalize file separator.
        $url = str_replace('\\', '/', sys_get_temp_dir());
        $url .= sprintf('/test%s.log', hash('sha1', random_bytes(16)));
        // PHP on Windows (ignores/fails to set) file permissions so all files end up with same thing.
        $perms = 0 === strpos(PHP_OS, 'WIN') ? 0666 : 0640;
        $args = [$url, Logger::DEBUG, true, $perms];
        $this->beConstructedWith(...$args);
        $this->handle($this->getRecord(Logger::WARNING, 'test'));
        Assert::same(fileperms($url) & 0777,
            $perms,
            'File permissions were not set correctly, might be caused by unusual umask value');
        $this->close();
        $tries = 10;
        do {
            /** @noinspection PhpUsageOfSilenceOperatorInspection */
            if (true === @unlink($url)) {
                break;
            }
            usleep(random_int(1000, 50000));
        } while (--$tries);
    }
    public function it_throws_exception_when_given_illegal_url_string_stream()
    {
        $url = 'bogus://http://www.example.org/';
        $this->beConstructedWith($url);
        $mess = sprintf('The stream or file "%s" could not be opened', $url);
        $this->shouldThrow(new \UnexpectedValueException($mess))
            ->duringHandle($this->getRecord(Logger::WARNING, 'test'));
    }
    public function it_throws_exception_when_given_non_existing_and_non_buildable_file_string_stream()
    {
        // First normalize file separator.
        $url = str_replace('\\', '/', sys_get_temp_dir());
        // Add really long random and hopefully impossible to build path.
        $url .= sprintf('/%stest.log', str_repeat(hash('sha1', random_bytes(8)) . '/', random_int(250, 500)));
        $this->beConstructedWith('file://' . $url);
        $mess = sprintf('There is no existing directory at "%s" and its not buildable', dirname($url));
        $this->shouldThrow(new \UnexpectedValueException($mess))
            ->duringHandle($this->getRecord(Logger::WARNING, 'test'));
    }
    public function it_throws_exception_when_given_non_existing_and_non_buildable_plain_string_stream()
    {
        // First normalize file separator.
        $url = str_replace('\\', '/', sys_get_temp_dir());
        // Add really long random and hopefully impossible to build path.
        $url .= sprintf('/%stest.log', str_repeat(hash('sha1', random_bytes(8)) . '/', random_int(250, 500)));
        $this->beConstructedWith($url);
        $mess = sprintf('There is no existing directory at "%s" and its not buildable', dirname($url));
        $this->shouldThrow(new \UnexpectedValueException($mess))
            ->duringHandle($this->getRecord(Logger::WARNING, 'test'));
        $mess = sprintf('The stream or file "%s" could not be opened', $url);
        $this->shouldThrow(new \UnexpectedValueException($mess))
            ->duringHandle($this->getRecord(Logger::WARNING, 'test'));
    }
    public function it_throws_exception_when_the_external_stream_is_empty_string()
    {
        $this->beConstructedWith('');
        $this->shouldThrow(new \InvalidArgumentException('A stream must either be a resource or a non-empty string'))
            ->duringInstantiation();
    }
    public function it_throws_exception_when_the_external_stream_is_null()
    {
        $this->beConstructedWith(null);
        $this->shouldThrow(new \InvalidArgumentException('A stream must either be a resource or a non-empty string'))
            ->duringInstantiation();
    }
    public function it_throws_exception_when_trying_to_write_after_closing_external_stream()
    {
        $handle = fopen('php://memory', 'ab+');
        $this->beConstructedWith($handle);
        $this->handle($this->getRecord(Logger::WARNING, 'test'));
        $this->getStream()
            ->shouldBeResource();
        $this->close();
        $this->shouldThrow(new \LogicException('Tried to write to an external stream after it was release by close'))
            ->duringHandle($this->getRecord(Logger::WARNING, 'test'));
    }
    /**
     * @param FormatterInterface|\PhpSpec\Wrapper\Collaborator $formatter
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Psr\Log\InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function it_uses_processor_during_handle(FormatterInterface $formatter)
    {
        /** @noinspection PhpParamsInspection */
        $formatter->format(Argument::type('array'))
            ->will(function ($args) {
                return $args[0]['message'] . ' ' . $args[0]['extra'][0];
            });
        $handle = fopen('php://memory', 'ab+');
        $this->beConstructedWith($handle);
        $this->setFormatter($formatter);
        $this->pushProcessor(function (array $record) {
            $record['extra'][] = 'processed';
            return $record;
        });
        $this->handle($this->getRecord(Logger::WARNING, 'test'));
        fseek($handle, 0);
        Assert::same(fread($handle, 100), 'test processed');
        fclose($handle);
    }
    public function it_will_create_new_path_and_or_local_stream_when_they_do_not_exist()
    {
        // First normalize file separator.
        $url = str_replace('\\', '/', sys_get_temp_dir());
        $url .= sprintf('/%s/test%s.log', hash('sha1', random_bytes(16)), hash('sha1', random_bytes(16)));
        clearstatcache(true);
        Assert::false(is_dir(dirname($url)), 'Random test directory should not exist yet');
        $this->beConstructedWith($url);
        $this->handle($this->getRecord(Logger::WARNING, 'test'));
        $this->close();
        Assert::true(is_dir(dirname($url)), 'Random test directory was not created');
        Assert::true(is_file($url), 'Random test file was not created');
        $tries = 10;
        do {
            /** @noinspection PhpUsageOfSilenceOperatorInspection */
            if (true === @unlink($url)) {
                break;
            }
            usleep(random_int(1000, 50000));
        } while (--$tries);
        clearstatcache(true);
        $tries = 10;
        do {
            /** @noinspection PhpUsageOfSilenceOperatorInspection */
            if (true === @unlink(dirname($url))) {
                break;
            }
            usleep(random_int(1000, 50000));
            clearstatcache(true);
        } while (--$tries);
    }
    public function let()
    {
        $this->beConstructedWith('php://memory');
    }
}
