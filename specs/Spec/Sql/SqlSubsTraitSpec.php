<?php
declare(strict_types = 1);
/**
 * Contains class SqlSubsTraitSpec.
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
namespace Spec\Yapeal\Sql;

use PhpSpec\ObjectBehavior;
use Yapeal\Container\ContainerInterface;

/**
 * Class SqlSubsTraitSpec
 *
 * @mixin \Yapeal\Sql\SqlSubsTrait
 * @mixin \Spec\Yapeal\Sql\MockSqlSubs
 *
 * @method void during($method, array $params)
 * @method void shouldBe($value)
 * @method void shouldContain($value)
 * @method void shouldNotEqual($value)
 * @method void shouldReturn($result)
 */
class SqlSubsTraitSpec extends ObjectBehavior
{
    /**
     * @param \PhpSpec\Wrapper\Collaborator|\Yapeal\Container\ContainerInterface $dic
     *
     * @throws \Prophecy\Exception\InvalidArgumentException
     */
    public function it_should_not_return_class_from_get_sql_subs_when_given_class_in_sql_section(
        ContainerInterface $dic
    ) {
        $dic->keys()
            ->willReturn(['Yapeal.Sql.platform', 'Yapeal.Sql.Classes.create', 'Yapeal.Fake.faked']);
        $dic->offsetGet('Yapeal.Sql.platform')
            ->willReturn('mysql');
        $dic->offsetGet('Yapeal.Sql.Classes.create')
            ->shouldNotBeCalled();
        $this->proxyGetSqlSubs($dic)
            ->shouldReturn(['{platform}' => 'mysql']);
    }
    /**
     * @param \PhpSpec\Wrapper\Collaborator|\Yapeal\Container\ContainerInterface $dic
     *
     * @throws \Prophecy\Exception\InvalidArgumentException
     */
    public function it_should_not_return_known_classes_from_get_sql_subs_when_given_one_in_sql_section(
        ContainerInterface $dic
    ) {
        $dic->keys()
            ->willReturn(['Yapeal.Sql.platform', 'Yapeal.Sql.Callable.CommonQueries', 'Yapeal.Fake.faked']);
        $dic->offsetGet('Yapeal.Sql.platform')
            ->willReturn('mysql');
        $dic->offsetGet('Yapeal.Sql.Callable.CommonQueries')
            ->shouldNotBeCalled();
        $this->proxyGetSqlSubs($dic)
            ->shouldReturn(['{platform}' => 'mysql']);
    }
    /**
     * @param \PhpSpec\Wrapper\Collaborator|\Yapeal\Container\ContainerInterface $dic
     *
     * @throws \Prophecy\Exception\InvalidArgumentException
     */
    public function it_should_not_return_wrong_platform_settings_from_get_sql_subs_when_given_multiple_platforms_settings(
        ContainerInterface $dic
    ) {
        $dic->keys()
            ->willReturn([
                'Yapeal.Sql.platform',
                'Yapeal.Sql.Platforms.mysql.userName',
                'Yapeal.Sql.Platforms.fake.userName'
            ]);
        $dic->offsetGet('Yapeal.Sql.platform')
            ->willReturn('mysql');
        $dic->offsetGet('Yapeal.Sql.Platforms.mysql.userName')
            ->willReturn('YapealUser');
        $dic->offsetGet('Yapeal.Sql.Platforms.fake.userName')
            ->shouldNotBeCalled();
        $this->proxyGetSqlSubs($dic)
            ->shouldReturn(['{platform}' => 'mysql', '{userName}' => 'YapealUser']);
    }
    public function it_should_remove_comments_in_get_cleaned_up_sql_when_given_sql_with_comments()
    {
        $given = /** @lang MySQL */
            "-- A comment\n--\nSELECT * FROM dummy;\n/* multi-\n line\n comment*/\n/** Doc-block */\n \n-- \n";
        $expect = /** @lang MySQL */
            'SELECT * FROM dummy;';
        $this->proxyGetCleanedUpSql($given, [])
            ->shouldReturn($expect);
    }
    /**
     * @param \PhpSpec\Wrapper\Collaborator|\Yapeal\Container\ContainerInterface $dic
     *
     * @throws \Prophecy\Exception\InvalidArgumentException
     */
    public function it_should_return_only_sql_section_from_get_sql_subs_when_given_dic_with_sql_and_other_sections(
        ContainerInterface $dic
    ) {
        $dic->keys()
            ->willReturn(['Yapeal.Sql.platform', 'Yapeal.Sql.password', 'Yapeal.Fake.faked']);
        $dic->offsetGet('Yapeal.Sql.platform')
            ->willReturn('mysql');
        $dic->offsetGet('Yapeal.Sql.password')
            ->willReturn('secret');
        $dic->offsetGet('Yapeal.Fake.faked')
            ->shouldNotBeCalled();
        $this->proxyGetSqlSubs($dic)
            ->shouldReturn(['{platform}' => 'mysql', '{password}' => 'secret']);
    }
    public function it_should_return_single_line_sql_statements_from_get_cleaned_up_sql_when_given_multiple_line_ones()
    {
        $given = /** @lang MySQL */
            <<<'SQL'
CREATE TABLE "dummy" (
    "id"      BIGINT(20) UNSIGNED  NOT NULL,
    "balance" DECIMAL(17, 2)       NOT NULL,
    PRIMARY KEY ("id")
);
SQL;
        $expect = /** @lang MySQL */
            'CREATE TABLE "dummy" ( "id"      BIGINT(20) UNSIGNED  NOT NULL,'
            . ' "balance" DECIMAL(17, 2)       NOT NULL, PRIMARY KEY ("id"));';
        $this->proxyGetCleanedUpSql($given, [])
            ->shouldReturn($expect);
        $given = $given . "\n" . $given;
        $expect = $expect . "\n" . $expect;
        $this->proxyGetCleanedUpSql($given, [])
            ->shouldReturn($expect);
    }
    /**
     * @param \PhpSpec\Wrapper\Collaborator|\Yapeal\Container\ContainerInterface $dic
     */
    public function let(ContainerInterface $dic)
    {
        $dic->keys();
        $this->beAnInstanceOf('\Spec\Yapeal\Sql\MockSqlSubs');
    }
}

