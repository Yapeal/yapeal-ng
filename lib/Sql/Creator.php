<?php
/**
 * Contains Creator class.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal which can be used to access the Eve Online
 * API data and place it into a database.
 * Copyright (C) 2015 Michael Cummings
 *
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General
 * Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option)
 * any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Lesser General Public License along with this program. If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * You should be able to find a copy of this license in the LICENSE.md file. A copy of the GNU GPL should also be
 * available in the GNU-GPL.md file.
 *
 * @copyright 2015 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Sql;

use SimpleXMLElement;
use SimpleXMLIterator;
use Symfony\Component\Console\Input\InputInterface;
use Yapeal\Console\Command\EveApiCreatorTrait;
use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\EventMediatorInterface;
use Yapeal\Log\Logger;
use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Class Creator
 */
class Creator
{
    use EveApiCreatorTrait;
    /**
     * @param EveApiEventInterface   $event
     * @param string                 $eventName
     * @param EventMediatorInterface $yem
     *
     * @return EveApiEventInterface
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function createEveApi(EveApiEventInterface $event, $eventName, EventMediatorInterface $yem)
    {
        $this->setYem($yem);
        $data = $event->getData();
        $this->getYem()
            ->triggerLogEvent(
                'Yapeal.Log.log',
                Logger::DEBUG,
                $this->getReceivedEventMessage($data, $eventName, __CLASS__)
            );
        // Only work with raw unaltered XML data.
        if (false !== strpos($data->getEveApiXml(), '<?yapeal.parameters.json')) {
            return $event->setHandledSufficiently();
        }
        $outputFile = sprintf(
            '%1$s%2$s/%3$s.sql',
            $this->getBaseDir(),
            $data->getEveApiSectionName(),
            $data->getEveApiName()
        );
        $apiName = $data->getEveApiName();
        $sectionName = $data->getEveApiSectionName();
        $xml = $data->getEveApiXml();
        $sxi = new SimpleXMLIterator($xml);
        $vars = ['tableName' => lcfirst($sectionName) . $apiName, 'columns' => $this->processValueOnly($sxi)];
        $options = [
            'elementsVO'   => $this->processValueOnly($sxi),
            'elementsWKNA' => $this->processWithKidsAndNoAttributes($sxi),
            'elementsNRS'  => $this->processNonRowset($sxi),
            'elementsRS'   => $this->processRowset($sxi)
        ];
    }
    /**
     * @param  array $columnNames
     * @param string $sectionName
     *
     * @return string
     */
    protected function getColumnList(array $columnNames, $sectionName)
    {
        if (in_array(strtolower($sectionName), ['char', 'corp', 'account'], true)) {
            $columnNames[] = 'ownerID';
        }
        $columnNames = array_unique($columnNames);
        sort($columnNames);
        $longestName = array_reduce(
            $columnNames,
            function ($arg1, $arg2) {
                return (strlen($arg1) > strlen($arg2)) ? $arg1 : $arg2;
            }
        );
        $maxWidth = strlen($longestName) + 2;
        $columns = [];
        foreach ($columnNames as $name) {
            $columns[] = sprintf('"%1$-' . $maxWidth . 's" %2$s', $name, $this->inferTypeFromName($name));
        }
        return implode(",\n" . str_repeat(' ', 4), $columns);
    }
    /**
     * @param string[] $keyNames
     * @param string   $sectionName
     *
     * @return string
     */
    protected function getSqlKeys(array $keyNames, $sectionName)
    {
        if (in_array(strtolower($sectionName), ['account', 'char', 'corp'], true)) {
            array_unshift($keyNames, 'ownerID');
        }
        $keyNames = array_unique($keyNames);
        return '"' . implode('", "', $keyNames) . '"';
    }
    /**
     * @param EveApiReadWriteInterface $data
     * @param InputInterface           $input
     *
     * @return array
     */
    protected function getSubs(EveApiReadWriteInterface $data, InputInterface $input)
    {
        $apiName = ucfirst($input->getArgument('api_name'));
        $sectionName = $input->getArgument('section_name');
        $sxi = new SimpleXMLIterator($data->getEveApiXml());
        $columnNames = [];
        $subs = [
            'columnList'   => $this->getColumnList($columnNames, $sectionName),
            'elementsVO'   => $this->processValueOnly($sxi),
            'elementsWKNA' => $this->processWithKidsAndNoAttributes($sxi),
            'elementsNRS'  => $this->processNonRowset($sxi),
            'elementsRS'   => $this->processRowset($sxi),
            'sectionName'  => ucfirst($sectionName),
            'tableName'    => lcfirst($sectionName) . $apiName
        ];
        return $subs;
    }
    /**
     * Used to infer(choose) type from element or attribute's name.
     *
     * @param string $name     Name of the element or attribute.
     * @param bool   $forValue Determines if returned type is going to be used for element or an attribute.
     *
     * @return string Returns the inferred type from the name.
     */
    protected function inferTypeFromName($name, $forValue = false)
    {
        $lcName = strtolower($name);
        $column = $forValue ? 'TEXT                NOT NULL' : 'VARCHAR(255) DEFAULT \'\'';
        foreach ([
                     'descr' => 'TEXT                NOT NULL',
                     'name'  => 'CHAR(100)           NOT NULL',
                     'tax'   => 'DECIMAL(17, 2)      NOT NULL',
                     'time'  => 'DATETIME            NOT NULL',
                     'date'  => 'DATETIME            NOT NULL'
                 ] as $search => $replace) {
            if (false !== strpos($lcName, $search)) {
                $column = $replace;
            }
        }
        if ('ID' === substr($name, -2)) {
            $column = 'BIGINT(20) UNSIGNED NOT NULL';
        }
        return $column;
    }
    /**
     * @param SimpleXMLIterator $sxi
     *
     * @return array
     */
    protected function processNonRowset(SimpleXMLIterator $sxi)
    {
        $elements = $sxi->xpath('//result/child::*[* and @* and not(@name|@key)]');
        if (0 === count($elements)) {
            return [];
        }
        return [];
    }
    /**
     * @param SimpleXMLIterator $sxi
     * @param string            $xPath
     *
     * @return array
     */
    protected function processRowset(SimpleXMLIterator $sxi, $xPath = '//result/rowset')
    {
        $elements = $sxi->xpath($xPath);
        if (0 === count($elements)) {
            return [];
        }
        return [];
    }
    /**
     * @param SimpleXMLIterator $sxi
     *
     * @return array
     */
    protected function processValueOnly(SimpleXMLIterator $sxi)
    {
        $elements = $sxi->xpath('//result/child::*[not(*|@*)]');
        if (0 === count($elements)) {
            return [];
        }
        $rows = [];
        /**
         * @type SimpleXMLElement $ele
         */
        foreach ($elements as $ele) {
            $name = (string)$ele->getName();
            $rows[$name] = $this->inferTypeFromName($name, true);
        }
        ksort($rows);
        return $rows;
    }
    /**
     * @param SimpleXMLIterator $sxi
     *
     * @return array
     */
    protected function processWithKidsAndNoAttributes(SimpleXMLIterator $sxi)
    {
        $elements = $sxi->xpath('//result/child::*[* and not(@*)]');
        if (0 === count($elements)) {
            return [];
        }
        $rows = [];
        return $rows;
    }
    /**
     * @type integer $tableCount
     */
    protected $tableCount = 0;
}
