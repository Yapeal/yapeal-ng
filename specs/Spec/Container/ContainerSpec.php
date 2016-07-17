<?php
/**
 * Contains class ContainerSpec.
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
 * <http://www.gnu.org/licenses/>.
 *
 * You should be able to find a copy of this license in the LICENSE.md file. A
 * copy of the GNU GPL should also be available in the GNU-GPL.md file.
 *
 * @copyright 2016 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Spec\Yapeal\Container;

use PhpSpec\Exception\Example\SkippingException;
use PhpSpec\ObjectBehavior;
use Spec\Yapeal\Invokable;
use Spec\Yapeal\MockService;
use Spec\Yapeal\NonInvokable;

/**
 * Class ContainerSpec
 *
 * @mixin \Yapeal\Container\Container
 *
 * @method void during($method, array $params)
 * @method void shouldBe($value)
 * @method void shouldContain($value)
 * @method void shouldHaveKey($key)
 * @method void shouldHaveType($value)
 * @method void shouldImplement($interface)
 * @method void shouldNotEqual($value)
 * @method void shouldReturn($result)
 */
class ContainerSpec extends ObjectBehavior
{
    public function itIsInitializable()
    {
        $this->shouldHaveType('Yapeal\Container\Container');
    }
    public function itProvidedFluentInterfaceFromRegister($provider)
    {
        /**
         * @var \Yapeal\Container\ServiceProviderInterface $provider
         */
        $provider->beADoubleOf('\Yapeal\Container\ServiceProviderInterface');
        $provider->register($this)
            ->willReturn();
        $this->register($provider)
            ->shouldReturn($this);
    }
    public function itShouldAllowDefiningNewServiceAfterFreezingFirst()
    {
        $this['foo'] = function () {
            return 'fooValue';
        };
        $this['foo'];
        $this['bar'] = function () {
            return 'barValue';
        };
        $this['bar']->shouldReturn('barValue');
    }
    public function itShouldAllowExtendingNonFrozenService()
    {
        $this['foo'] = function () {
            return 'foo';
        };
        $this['foo'] = $this->extend('foo',
            function ($foo) {
                return "$foo.bar";
            });
        $this['foo'] = $this->extend('foo',
            function ($foo) {
                return "$foo.baz";
            });
        $this['foo']->shouldReturn('foo.bar.baz');
    }
    public function itShouldAllowExtendingOtherServiceAfterFreezingFirst()
    {
        $this['foo'] = function () {
            return 'foo';
        };
        $this['bar'] = function () {
            return 'bar';
        };
        $this['foo'];
        $this['bar'] = $this->extend('bar',
            function ($bar, $app) {
                return "$bar.baz";
            });
        $this['bar']->shouldReturn('bar.baz');
    }
    public function itShouldAllowGlobalFunctionNamesAsParameterValue()
    {
        $globals = ['strlen', 'count', 'strtolower'];
        foreach ($globals as $global) {
            $this['global_function'] = $global;
            $this['global_function']->shouldReturn($global);
        }
    }
    public function itShouldAllowRemovingFrozenServiceAndThenSettingAgain()
    {
        $this['foo'] = function () {
            return 'fooValue';
        };
        $this['foo'];
        unset($this['foo']);
        $this['foo'] = function () {
            return 'barValue';
        };
    }
    public function itShouldAlsoUnsetKeyWhenUnsettingOffsets()
    {
        $this['param'] = 'value';
        $this['service'] = function () {
            return new MockService();
        };
        $this->keys()
            ->shouldHaveCount(2);
        unset($this['param'], $this['service']);
        $this->keys()
            ->shouldHaveCount(0);
    }
    public function itShouldHaveAnOffsetAfterOneIsSet()
    {
        $this->keys()
            ->shouldHaveCount(0);
        $this['param'] = 'value';
        $this->keys()
            ->shouldContain('param');
        $this->keys()
            ->shouldHaveCount(1);
    }
    public function itShouldHonorNullValuesInOffsetGet()
    {
        $this['foo'] = null;
        $this['foo']->shouldReturn(null);
    }
    public function itShouldHonorReturningNullValuesFromRaw()
    {
        $this['foo'] = null;
        $this->raw('foo')
            ->shouldReturn(null);
    }
    public function itShouldInitialiseOffsetsFromConstructor()
    {
        $params = ['param' => 'value', 'param2' => false];
        $this->beConstructedWith($params);
        $this->keys()
            ->shouldHaveCount(2);
        foreach ($params as $param => $value) {
            $this[$param]->shouldBe($value);
        }
    }
    public function itShouldNotInvokeProtectedServices()
    {
        $services = [
            function ($value) {
                $service = new MockService();
                $service->value = $value;
                return $service;
            },
            new Invokable()
        ];
        foreach ($services as $service) {
            $this['protected'] = $this->protect($service);
            $this['protected']->shouldReturn($service);
        }
    }
    public function itShouldPassContainerAsParameter()
    {
        $this['service'] = function () {
            return new MockService();
        };
        $this['container'] = function ($container) {
            return $container;
        };
        $this['service']->shouldNotEqual($this);
        $this['container']->shouldEqual($this);
    }
    public function itShouldReturnDifferentInstancesOfSameTypeFromFactory()
    {
        $this['service'] = $this->factory(function () {
            return new MockService();
        });
        $serviceOne = $this['service']->shouldHaveType('Spec\Yapeal\MockService');
        $this['service']->shouldNotEqual($serviceOne);
    }
    public function itShouldReturnOriginalInstanceFromRawWhenUsingFactory()
    {
        $definition = $this->factory(function () {
            return 'foo';
        });
        $this['service'] = $definition;
        $this->raw('service')
            ->shouldReturn($definition);
        $this['service']->shouldNotReturn($definition);
    }
    public function itShouldReturnSameInstanceAndTypeAsCallableReturns()
    {
        $this['service'] = function () {
            return new MockService();
        };
        $serviceOne = $this['service']->shouldHaveType('Spec\Yapeal\MockService');
        $this['service']->shouldEqual($serviceOne);
    }
    public function itShouldReturnSameTypeAndValueForSimpleValues()
    {
        foreach ([true, false, null, 'value', 1, 1.0, ['value']] as $item) {
            $this['param'] = $item;
            $this['param']->shouldBe($item);
        }
    }
    public function itShouldReturnTrueFromOffsetExistsForAnySetKey()
    {
        $this['param'] = 'value';
        $this['service'] = function () {
            return new MockService();
        };
        $this['null'] = null;
        $this->offsetExists('param')
            ->shouldReturn(true);
        $this->offsetExists('param')
            ->shouldReturn(isset($this['param']));
        $this->offsetExists('service')
            ->shouldReturn(true);
        $this->offsetExists('service')
            ->shouldReturn(isset($this['service']));
        $this->offsetExists('null')
            ->shouldReturn(true);
        $this->offsetExists('null')
            ->shouldReturn(isset($this['null']));
        $this->offsetExists('non_existent')
            ->shouldNotReturn(true);
        $this->offsetExists('non_existent')
            ->shouldNotReturn(isset($this['non_existent']));
    }
    public function itShouldTreatInvokableObjectLikeCallable()
    {
        $this['invokable'] = new Invokable();
        $invoked = $this['invokable']->shouldHaveType('Spec\Yapeal\MockService');
        $this['invokable']->shouldReturn($invoked);
    }
    public function itShouldTreatNonInvokableObjectLikeParameter()
    {
        $this['non_invokable'] = new NonInvokable();
        $this['non_invokable']->shouldHaveType('Spec\Yapeal\NonInvokable');
    }
    public function itThrowsExceptionForNonExistentExtendOffset()
    {
        $id = 'param';
        $mess = sprintf('Identifier "%s" is not defined.', $id);
        $this->shouldThrow(new \InvalidArgumentException($mess))
            ->during('extend',
                [
                    $id,
                    function () {
                    }
                ]);
    }
    public function itThrowsExceptionForNonExistentGetOffset()
    {
        $id = 'param';
        $mess = sprintf('Identifier "%s" is not defined.', $id);
        $this->shouldThrow(new \InvalidArgumentException($mess))
            ->during('offsetGet', [$id]);
    }
    public function itThrowsExceptionForNonExistentRawOffset()
    {
        $id = 'param';
        $mess = sprintf('Identifier "%s" is not defined.', $id);
        $this->shouldThrow(new \InvalidArgumentException($mess))
            ->during('raw', [$id]);
    }
    public function itThrowsExceptionWhenExtendIsGivenNonInvokable()
    {
        if (version_compare(PHP_VERSION, '7.0.0', '>=')) {
            throw new SkippingException('Unneeded on PHP 7.x or higher caught by TypeError');
        }
        $this['foo'] = function () {
            return 'foo';
        };
        $this->shouldThrow(new \InvalidArgumentException('Extension service definition is not a Closure or invokable object.'))
            ->during('extend', ['foo', new NonInvokable()]);
    }
    public function itThrowsExceptionWhenFactoryIsGivenNonInvokable()
    {
        if (version_compare(PHP_VERSION, '7.0.0', '>=')) {
            throw new SkippingException('Unneeded on PHP 7.x or higher caught by TypeError');
        }
        $this['service'] = $this->shouldThrow(new \InvalidArgumentException('Service definition is not a Closure or invokable object.'))
            ->during('factory', [123]);
        $this['service'] = $this->shouldThrow(new \InvalidArgumentException('Service definition is not a Closure or invokable object.'))
            ->during('factory', [new NonInvokable()]);
    }
    public function itThrowsExceptionWhenProtectIsGivenNonInvokable()
    {
        if (version_compare(PHP_VERSION, '7.0.0', '>=')) {
            throw new SkippingException('Unneeded on PHP 7.x or higher caught by TypeError');
        }
        $this->shouldThrow(new \InvalidArgumentException('Callable is not a Closure or invokable object.'))
            ->during('protect', [new NonInvokable()]);
    }
    public function itThrowsExceptionWhenTryingToExtendNonInvokable()
    {
        $this['param'] = 123;
        $this['non_invokable'] = new NonInvokable();
        $this['param'] = $this->shouldThrow(new \InvalidArgumentException('Identifier "param" does not contain an object definition.'))
            ->during('extend',
                [
                    'param',
                    function () {
                    }
                ]);
        $this['non_invokable'] = $this->shouldThrow(new \InvalidArgumentException('Identifier "non_invokable" does not contain an object definition.'))
            ->during('extend',
                [
                    'non_invokable',
                    function () {
                    }
                ]);
    }
    public function itThrowsExceptionWhenTryingToExtendingFrozenService()
    {
        $this['foo'] = function () {
            return 'foo';
        };
        $this['foo'] = $this->extend('foo',
            function ($foo) {
                return "$foo.bar";
            });
        $this['foo']->shouldReturn('foo.bar');
        $this->shouldThrow(new \InvalidArgumentException('Identifier "foo" does not contain an object definition.'))
            ->during('extend',
                [
                    'foo',
                    function ($foo) {
                        return "$foo.baz";
                    }
                ]);
    }
    public function itThrowsExceptionWhenTryingToGetOffsetAfterTheyHaveBeenUnset()
    {
        $this['param'] = 'value';
        $this['service'] = function () {
            return new MockService();
        };
        $this->keys()
            ->shouldHaveCount(2);
        unset($this['param'], $this['service']);
        $this->shouldThrow(new \InvalidArgumentException('Identifier "param" is not defined.'))
            ->during('offsetGet', ['param']);
        $this->shouldThrow(new \InvalidArgumentException('Identifier "service" is not defined.'))
            ->during('offsetGet', ['service']);
    }
    public function itThrowsExceptionWhenTryingToOverWriteFrozenService()
    {
        $this['foo'] = function () {
            return 'foo';
        };
        $this['foo'];
        $this->shouldThrow(new \RuntimeException('Cannot override frozen service "foo".'))
            ->during('offsetSet',
                [
                    'foo',
                    function () {
                        return 'bar';
                    }
                ]);
    }
}
