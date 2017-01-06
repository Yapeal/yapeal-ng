<?php
declare(strict_types = 1);
/**
 * Contains class ContainerSpec.
 *
 * PHP version 7.0
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Spec
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
 * @copyright 2016-2017 Michael Cummings
 * @license   LGPL-3.0+
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Spec\Yapeal\Container;

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
    public function it_is_initializable()
    {
        $this->shouldHaveType('Yapeal\Container\Container');
    }
    /**
     * @param \Yapeal\Container\ServiceProviderInterface|\PhpSpec\Wrapper\Collaborator $provider
     *
     * @throws \Prophecy\Exception\Doubler\ClassNotFoundException
     * @throws \Prophecy\Exception\Doubler\DoubleException
     * @throws \Prophecy\Exception\Doubler\InterfaceNotFoundException
     * @throws \Prophecy\Exception\InvalidArgumentException
     */
    public function it_provided_fluent_interface_from_register($provider)
    {
        $provider->beADoubleOf('\Yapeal\Container\ServiceProviderInterface');
        $provider->register($this)
            ->willReturn();
        $this->register($provider)
            ->shouldReturn($this);
    }
    public function it_should_allow_defining_new_service_after_freezing_first()
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
    public function it_should_allow_extending_non_frozen_service()
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
    public function it_should_allow_extending_other_service_after_freezing_first()
    {
        $this['foo'] = function () {
            return 'foo';
        };
        $this['bar'] = function () {
            return 'bar';
        };
        $this['foo'];
        $this['bar'] = $this->extend('bar',
            function ($bar) {
                return "$bar.baz";
            });
        $this['bar']->shouldReturn('bar.baz');
    }
    public function it_should_allow_global_function_names_as_parameter_value()
    {
        $globals = ['strlen', 'count', 'strtolower'];
        foreach ($globals as $global) {
            $this['global_function'] = $global;
            $this['global_function']->shouldReturn($global);
        }
    }
    public function it_should_allow_removing_frozen_service_and_then_setting_again()
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
    public function it_should_also_unset_key_when_unsetting_offsets()
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
    public function it_should_have_an_offset_after_one_is_set()
    {
        $this->keys()
            ->shouldHaveCount(0);
        $this['param'] = 'value';
        $this->keys()
            ->shouldContain('param');
        $this->keys()
            ->shouldHaveCount(1);
    }
    public function it_should_honor_null_values_in_offset_get()
    {
        $this['foo'] = null;
        $this['foo']->shouldReturn(null);
    }
    public function it_should_honor_returning_null_values_from_raw()
    {
        $this['foo'] = null;
        $this->raw('foo')
            ->shouldReturn(null);
    }
    public function it_should_initialise_offsets_from_constructor()
    {
        $params = ['param' => 'value', 'param2' => false];
        $this->beConstructedWith($params);
        $this->keys()
            ->shouldHaveCount(2);
        foreach ($params as $param => $value) {
            $this[$param]->shouldBe($value);
        }
    }
    public function it_should_not_invoke_protected_services()
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
    public function it_should_pass_container_as_parameter()
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
    public function it_should_return_different_instances_of_same_type_from_factory()
    {
        $this['service'] = $this->factory(function () {
            return new MockService();
        });
        $serviceOne = $this['service']->shouldHaveType('Spec\Yapeal\MockService');
        $this['service']->shouldNotEqual($serviceOne);
    }
    public function it_should_return_original_instance_from_raw_when_using_factory()
    {
        $definition = $this->factory(function () {
            return 'foo';
        });
        $this['service'] = $definition;
        $this->raw('service')
            ->shouldReturn($definition);
        $this['service']->shouldNotReturn($definition);
    }
    public function it_should_return_same_instance_and_type_as_callable_returns()
    {
        $this['service'] = function () {
            return new MockService();
        };
        $serviceOne = $this['service']->shouldHaveType('Spec\yapeal\MockService');
        $this['service']->shouldEqual($serviceOne);
    }
    public function it_should_return_same_type_and_value_for_simple_values()
    {
        foreach ([true, false, null, 'value', 1, 1.0, ['value']] as $item) {
            $this['param'] = $item;
            $this['param']->shouldBe($item);
        }
    }
    public function it_should_return_true_from_offset_exists_for_any_set_key()
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
    public function it_should_treat_invokable_object_like_callable()
    {
        $this['invokable'] = new Invokable();
        $invoked = $this['invokable']->shouldHaveType('Spec\Yapeal\MockService');
        $this['invokable']->shouldReturn($invoked);
    }
    public function it_should_treat_non_invokable_object_like_parameter()
    {
        $this['non_invokable'] = new NonInvokable();
        $this['non_invokable']->shouldHaveType('Spec\Yapeal\NonInvokable');
    }
    public function it_throws_exception_for_non_existent_extend_offset()
    {
        $id1 = 'param';
        $mess = sprintf('Identifier "%s" is not defined.', $id1);
        $this->shouldThrow(new \InvalidArgumentException($mess))
            ->during('extend',
                [
                    $id1,
                    function () {
                    }
                ]);
    }
    public function it_throws_exception_for_non_existent_get_offset()
    {
        $id1 = 'param';
        $mess = sprintf('Identifier "%s" is not defined.', $id1);
        $this->shouldThrow(new \InvalidArgumentException($mess))
            ->during('offsetGet', [$id1]);
    }
    public function it_throws_exception_for_non_existent_raw_offset()
    {
        $id1 = 'param';
        $mess = sprintf('Identifier "%s" is not defined.', $id1);
        $this->shouldThrow(new \InvalidArgumentException($mess))
            ->during('raw', [$id1]);
    }
    public function it_throws_exception_when_trying_to_extend_non_invokable()
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
    public function it_throws_exception_when_trying_to_extending_frozen_service()
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
    public function it_throws_exception_when_trying_to_get_offset_after_they_have_been_unset()
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
    public function it_throws_exception_when_trying_to_over_write_frozen_service()
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
