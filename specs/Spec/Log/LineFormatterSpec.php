<?php
declare(strict_types = 1);
/**
 * Contains class LineFormatterSpec.
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

use PhpSpec\Exception\Example\FailureException;
use PhpSpec\ObjectBehavior;
use Webmozart\Assert\Assert;
use Yapeal\Log\LineFormatter;

//use Prophecy\Argument;
/**
 * Class LineFormatterSpec
 *
 * @mixin \Yapeal\Log\LineFormatter
 *
 * @method void during($method, array $params)
 * @method void shouldBe($value)
 * @method void shouldContain($value)
 * @method void shouldNotEqual($value)
 * @method void shouldReturn($result)
 */
class LineFormatterSpec extends ObjectBehavior
{
    public function it_does_batch_formatting()
    {
        $given = [
            [
                'level_name' => 'CRITICAL',
                'channel' => 'test',
                'context' => [],
                'message' => 'bar',
                'datetime' => new \DateTime(),
                'extra' => []
            ],
            [
                'level_name' => 'WARNING',
                'channel' => 'log',
                'context' => [],
                'message' => 'foo',
                'datetime' => new \DateTime(),
                'extra' => []
            ]
        ];
        $expected = '['
            . date('Y-m-d')
            . '] test.CRITICAL: bar [] []'
            . "\n"
            . '['
            . date('Y-m-d')
            . '] log.WARNING: foo [] []'
            . "\n";
        $this->formatBatch($given)
            ->shouldReturn($expected);
    }
    public function it_does_context_and_extras_replacements()
    {
        $this->beConstructedWith('%context.foo% => %extra.foo%%context.iDoNotExist%');
        $given = [
            'level_name' => 'ERROR',
            'channel' => 'meh',
            'context' => ['foo' => 'bar'],
            'datetime' => new \DateTime(),
            'extra' => ['foo' => null],
            'message' => 'log'
        ];
        $expected = 'bar => NULL';
        $this->format($given)
            ->shouldReturn($expected);
    }
    public function it_formats_array_context_correctly_using_default_format()
    {
        $given = [
            'level_name' => 'ERROR',
            'channel' => 'meh',
            'message' => 'foo',
            'datetime' => new \DateTime(),
            'extra' => [],
            'context' => [
                'foo' => 'bar',
                'baz' => 'qux',
                'bool' => false,
                'null' => null
            ]
        ];
        $expected = '['
            . date('Y-m-d')
            . '] meh.ERROR: foo {"foo":"bar","baz":"qux","bool":false,"null":null} []'
            . "\n";
        $this->format($given)
            ->shouldReturn($expected);
    }
    public function it_formats_array_context_correctly_using_default_format_when_pretty_printing()
    {
        $given = [
            'level_name' => 'ERROR',
            'channel' => 'meh',
            'message' => 'foo',
            'datetime' => new \DateTime(),
            'extra' => [],
            'context' => [
                'foo' => 'bar',
                'baz' => 'qux',
                'bool' => false,
                'null' => null
            ]
        ];
        $expected = <<<EXPECTED
[%s] meh.ERROR: foo {
    "foo": "bar",
    "baz": "qux",
    "bool": false,
    "null": null
} []

EXPECTED;
        $expected = sprintf($expected, date('Y-m-d'));
        $this->setPrettyJson();
        $this->format($given)
            ->shouldReturn($expected);
    }
    public function it_formats_context_and_extras_correctly_when_using_ignore_empty()
    {
        $arguments = [null, 'Y-m-d', false, true];
        $this->beConstructedWith(...$arguments);
        $given = [
            'level_name' => 'ERROR',
            'channel' => 'meh',
            'context' => [],
            'datetime' => new \DateTime(),
            'extra' => [],
            'message' => 'log'
        ];
        $expected = '[' . date('Y-m-d') . '] meh.ERROR: log  ' . "\n";
        $this->format($given)
            ->shouldReturn($expected);
    }
    public function it_formats_exception_context_correctly_using_default_format()
    {
        $given = [
            'level_name' => 'CRITICAL',
            'channel' => 'core',
            'context' => ['exception' => new \RuntimeException('Foo')],
            'datetime' => new \DateTime(),
            'extra' => [],
            'message' => 'foobar'
        ];
        $expected = sprintf('[%s] core.CRITICAL: foobar {"exception":{"[exception]:":{"RuntimeException":{'
                . '"message":"Foo","code":0,"file":"%s:%s"}}}} []',
                date('Y-m-d'),
                str_replace('\\', '/', __FILE__),
                (__LINE__ - 9)) . "\n";
        $this->format($given)
            ->shouldReturn($expected);
    }
    public function it_formats_exception_context_with_back_trace_correctly()
    {
        $given = [
            'level_name' => 'CRITICAL',
            'channel' => 'core',
            'context' => ['exception' => (new TestBackTrace())->iThrowUp()],
            'datetime' => new \DateTime(),
            'extra' => [],
            'message' => 'foobar'
        ];
        // So we have something at least somewhat understandable with as little of PhpSpec's Cr@p getting in the way as
        // possible we're going to unwrap it.
        $formatter = $this->getWrappedObject();
        $formatter->setIncludeStackTraces(true);
        $result = $formatter->format($given);
        // Check the beginning.
        $expected = sprintf('[%s] core.CRITICAL: foobar {"exception":{"[exception]:":{"RuntimeException":{'
            . '"message":"Really?!?","code":1,"file":"',
            date('Y-m-d'));
        Assert::startsWith($result, $expected);
        // Check some of the important stuff in the middle.
        $expected = ',"trace":[{"class:":{"Spec\\\\Yapeal\\\\Log\\\\TestBackTrace":{"function":"iThrowUp","line":';
        Assert::contains($result, $expected);
        $expected = '"previous":{"[exception]:":{"LogicException":{"message":"I threw up on you!","code":0,"file":"';
        Assert::contains($result, $expected);
        // Finally check a little bit at the end.
        $expected = "}}}} []\n";
        Assert::endsWith($result, $expected);
    }
    public function it_formats_exception_context_with_previous_correctly_using_default_format()
    {
        $previous = new \LogicException('Wut?');
        $given = [
            'level_name' => 'CRITICAL',
            'channel' => 'core',
            'context' => ['exception' => new \RuntimeException('Foo', 0, $previous)],
            'datetime' => new \DateTime(),
            'extra' => [],
            'message' => 'foobar'
        ];
        $expected = sprintf('[%s] core.CRITICAL: foobar {"exception":{"[exception]:":{"RuntimeException":{'
                . '"message":"Foo","code":0,"file":"%s:%s",'
                . '"previous":{"[exception]:":{"LogicException":{"message":"Wut?","code":0,"file":"%s:%s"}}}}}}} []',
                date('Y-m-d'),
                str_replace('\\', '/', __FILE__),
                (__LINE__ - 10),
                str_replace('\\', '/', __FILE__),
                (__LINE__ - 16)) . "\n";
        $this->format($given)
            ->shouldReturn($expected);
    }
    public function it_formats_extras_correctly_using_custom_format()
    {
        $arguments = ["[%datetime%] %channel%.%level_name%: %message% %context% %extra.file% %extra%\n", 'Y-m-d'];
        $this->beConstructedWith(...$arguments);
        $given = [
            'level_name' => 'ERROR',
            'channel' => 'meh',
            'context' => [],
            'datetime' => new \DateTime(),
            'extra' => ['ip' => '127.0.0.1', 'file' => 'test'],
            'message' => 'log'
        ];
        $expected = '[' . date('Y-m-d') . '] meh.ERROR: log [] test {"ip":"127.0.0.1"}' . "\n";
        $this->format($given)
            ->shouldReturn($expected);
    }
    public function it_formats_extras_correctly_using_default_format()
    {
        $given = [
            'level_name' => 'ERROR',
            'channel' => 'meh',
            'context' => [],
            'datetime' => new \DateTime(),
            'extra' => ['ip' => '127.0.0.1'],
            'message' => 'log'
        ];
        $expected = '[' . date('Y-m-d') . '] meh.ERROR: log [] {"ip":"127.0.0.1"}' . "\n";
        $this->format($given)
            ->shouldReturn($expected);
    }
    public function it_formats_floats_correctly_even_when_given_nan_or_inf()
    {
        $given = [
            'level_name' => 'ERROR',
            'channel' => 'meh',
            'context' => ['foo' => 1.0],
            'datetime' => new \DateTime(),
            'extra' => ['bar' => log(0, 2),
                'baz' => acos(8)],
            'message' => 'log'
        ];
        $expected = '[' . date('Y-m-d') . '] meh.ERROR: log {"foo":1.0} {"bar":"-INF","baz":"NaN"}' . "\n";
        $this->format($given)
            ->shouldReturn($expected);
    }
    public function it_formats_object_extras_correctly_using_default_format()
    {
        $given = [
            'level_name' => 'ERROR',
            'channel' => 'meh',
            'context' => [],
            'datetime' => new \DateTime(),
            'extra' => [
                'foo' => new TestFoo(),
                'bar' => new TestBar(),
                'baz' => new TestBaz(),
                'anon' => function () {
                },
                'res' => fopen('php://memory', 'rb')
            ],
            'message' => 'foobar'
        ];
        $expected = '['
            . date('Y-m-d')
            . '] meh.ERROR: foobar [] {'
            . '"foo":{"[class]:":{"Spec\\\\Yapeal\\\\Log\\\\TestFoo":{"properties":{"foo":"foo"}}}},'
            . '"bar":{"[class]:":{"Spec\\\\Yapeal\\\\Log\\\\TestBar":"I am bar"}},'
            . '"baz":{"[class]:":{"Spec\\\\Yapeal\\\\Log\\\\TestBaz":{"methods":["baz"],"properties":{"foo":"foo"}}}},'
            . '"anon":{"[function]:":"(closure)"},'
            . '"res":{"[resource]:":"(stream)"}'
            . "}\n";
        $this->format($given)
            ->shouldReturn($expected);
    }
    public function it_formats_over_sized_array_correctly()
    {
        $largeArray = range(1, 2000);
        $given = [
            'level_name' => 'CRITICAL',
            'channel' => 'test',
            'message' => 'bar',
            'context' => [$largeArray],
            'datetime' => new \DateTime(),
            'extra' => []
        ];
        $this->format($given)
            ->shouldEndWith('"999":1000,"...":"Over 1000 items (2000 total), aborting normalization"}] []' . "\n");
    }
    public function it_formats_string_correctly_using_default_format()
    {
        $given = [
            'level_name' => 'WARNING',
            'channel' => 'log',
            'context' => [],
            'message' => 'foo',
            'datetime' => new \DateTime(),
            'extra' => []
        ];
        $expected = '[' . date('Y-m-d') . '] log.WARNING: foo [] []' . "\n";
        $this->format($given)
            ->shouldReturn($expected);
    }
    /**
     * Take from test in original class and not totally sure it's needed since it seems to be related to old json bug.
     *
     * @throws \InvalidArgumentException
     * @throws \PhpSpec\Exception\Fracture\ClassNotFoundException
     */
    public function it_ignores_recursive_object_references()
    {
        // Set up the recursion.
        $foo = new \stdClass();
        $bar = new \stdClass();
        $foo->bar = $bar;
        $bar->foo = $foo;
        // Set an error handler to assert that the error is not raised anymore.
        /** @noinspection PhpTooManyParametersInspection */
        set_error_handler(function ($level, $message, ...$unused) {
            extract($unused);
            if (0 < $level) {
                restore_error_handler();
                throw new FailureException("$message should not be raised");
            }
        });
        $formatter = $this->getWrappedObject();
        $refMethod = new \ReflectionMethod($formatter, 'toJson');
        $refMethod->setAccessible(true);
        $res = $refMethod->invoke($formatter, [$foo, $bar], true);
        restore_error_handler();
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        Assert::same((string)@json_encode([$foo, $bar]), $res);
    }
    public function it_is_initializable()
    {
        $this->shouldHaveType(LineFormatter::class);
    }
    public function it_should_not_strip_inline_line_breaks_when_flag_is_set()
    {
        $arguments = [null, 'Y-m-d', true];
        $this->beConstructedWith(...$arguments);
        $given = [
            'level_name' => 'WARNING',
            'channel' => 'log',
            'context' => [],
            'message' => "foo\nbar",
            'datetime' => new \DateTime(),
            'extra' => []
        ];
        $expected = '[' . date('Y-m-d') . "] log.WARNING: foo\nbar [] []\n";
        $this->format($given)
            ->shouldReturn($expected);
    }
    public function it_should_strip_inline_line_breaks_by_default()
    {
        $given = [
            'level_name' => 'WARNING',
            'channel' => 'log',
            'context' => [],
            'message' => "foo\nbar",
            'datetime' => new \DateTime(),
            'extra' => []
        ];
        $expected = '[' . date('Y-m-d') . "] log.WARNING: foo bar [] []\n";
        $this->format($given)
            ->shouldReturn($expected);
    }
    /**
     * @param \PhpSpec\Wrapper\Collaborator|TestToStringError $toStringError
     *
     * @throws \Prophecy\Exception\InvalidArgumentException
     */
    public function it_throws_exception_on_bad_to_string_method(TestToStringError $toStringError)
    {
        $exc = new \RuntimeException('Could not convert to string');
        $toStringError->__toString()
            ->willThrow($exc);
        $this->shouldThrow($exc)
            ->duringFormat(['myObject' => $toStringError]);
    }
    public function let()
    {
        $this->beConstructedWith(null, 'Y-m-d');
    }
}
