<?php
declare(strict_types=1);
/**
 * Contains class PreserverTraitSpec.
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
//use Prophecy\Argument;
use Yapeal\Container\ContainerInterface;

/**
 * Class PreserverTraitSpec
 *
 * @mixin \Yapeal\Sql\PreserverTrait
 * @mixin \Spec\Yapeal\Sql\MockPreserver
 *
 * @method void during($method, array $params)
 * @method void shouldBe($value)
 * @method void shouldContain($value)
 * @method void shouldNotEqual($value)
 * @method void shouldReturn($result)
 */
class PreserverTraitSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldImplement('Yapeal\Event\EveApiPreserverInterface');
    }
    /**
     * @param \PhpSpec\Wrapper\Collaborator|\Yapeal\Container\ContainerInterface $dic
     */
    public function let(ContainerInterface $dic)
    {
        $dic->keys();
        $this->beAnInstanceOf('\Spec\Yapeal\Sql\MockPreserver');
        $this->sxe = new \SimpleXMLElement($this->testCorpSheet);
    }
    /**
     * @var string $testCorpSheet
     */
    protected $testCorpSheet = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<eveapi version="6">
    <currentTime>2022-11-05 17:10:57</currentTime>
    <result>
        <allianceID>1</allianceID>
        <!--Optional:-->
        <allianceName>TestAllianceName</allianceName>
        <ceoID>2</ceoID>
        <ceoName>TestCeoName</ceoName>
        <corporationID>3</corporationID>
        <corporationName>TestCorporationName</corporationName>
        <description>Test description</description>
        <factionID>4</factionID>
        <!--Optional:-->
        <!--<factionName>TestFactionName</factionName>-->
        <memberCount>5</memberCount>
        <!--Optional:-->
        <memberLimit>6</memberLimit>
        <shares>7</shares>
        <stationID>8</stationID>
        <stationName>TestStationName</stationName>
        <taxRate>5.00</taxRate>
        <ticker>TCN</ticker>
        <url>http://localhost/</url>
        <logo>
            <graphicID>9</graphicID>
            <shape1>10</shape1>
            <shape2>11</shape2>
            <shape3>12</shape3>
            <color1>13</color1>
            <color2>14</color2>
            <color3>15</color3>
        </logo>
        <!--Optional:-->
        <divisions columns="accountKey,description" key="accountKey">
            <row accountKey="1000" description="string1"/>
            <row accountKey="1001" description="string2"/>
            <row accountKey="1002" description="string3"/>
            <row accountKey="1003" description="string4"/>
            <row accountKey="1004" description="string5"/>
            <row accountKey="1005" description="string6"/>
            <row accountKey="1006" description="string7"/>
        </divisions>
        <!--Optional:-->
        <walletDivisions columns="accountKey,description" key="accountKey">
            <!--7 to 8 repetitions:-->
            <row accountKey="1000" description="string1"/>
            <row accountKey="1001" description="string2"/>
            <row accountKey="1002" description="string3"/>
            <row accountKey="1003" description="string4"/>
            <row accountKey="1004" description="string5"/>
            <row accountKey="1005" description="string6"/>
            <row accountKey="1006" description="string7"/>
        </walletDivisions>
    </result>
    <cachedUntil>2022-11-05 17:15:57</cachedUntil>
</eveapi>
XML;
    /**
     * @var \SimpleXMLElement $sxe
     */
    private $sxe;
    public function it_should_convert_data_rows_into_sql_upsert_in_attribute_preserve_data()
    {
        $defaults = ['accountKey' => null, 'description' => null, 'ownerID'=> 3];
        $rows = $this->sxe->xpath('//divisions/row');
        $tableName = 'corpDivisions';

    }
}
