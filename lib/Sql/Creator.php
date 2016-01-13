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
use Twig_Environment;
use Twig_Error;
use Yapeal\Console\Command\EveApiCreatorTrait;
use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\MediatorInterface;
use Yapeal\Exception\YapealException;
use Yapeal\Log\Logger;
use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Class Creator
 */
class Creator
{
    use EveApiCreatorTrait;
    /**
     * Creator constructor.
     *
     * @param Twig_Environment $twig
     * @param string           $dir
     * @param string           $platform
     */
    public function __construct(Twig_Environment $twig, $dir = __DIR__, $platform = 'MySql')
    {
        $this->setDir($dir);
        $this->setPlatform($platform);
        $this->setTwig($twig);
    }
    /**
     * @param EveApiEventInterface $event
     * @param string               $eventName
     * @param MediatorInterface    $yem
     *
     * @return EveApiEventInterface
     * @throws YapealException
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function createSql(EveApiEventInterface $event, $eventName, MediatorInterface $yem)
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
        $this->sectionName = $data->getEveApiSectionName();
        $this->apiName = $data->getEveApiName();
        $outputFile = sprintf(
            '%1$s%2$s/Create%3$s.sql',
            $this->getDir(),
            ucfirst($this->sectionName),
            ucfirst($this->apiName)
        );
        // Nothing to do if NOT overwriting and file exists.
        if (false === $this->isOverwrite() && is_file($outputFile)) {
            return $event;
        }
        $sxi = new SimpleXMLIterator($data->getEveApiXml());
        $this->tables = [];
        $this->processValueOnly($sxi, lcfirst($this->apiName));
        $this->processRowset($sxi);
        $tCount = count($this->tables);
        if (0 === $tCount) {
            $mess = 'No SQL tables to create for';
            $this->getYem()->triggerLogEvent('Yapeal.Log.log', Logger::NOTICE, $this->createEveApiMessage($mess, $data));
        }
        $tableNames = array_keys($this->tables);
        if (1 === $tCount) {
            $this->tables[lcfirst($this->apiName)] = $this->tables[$tableNames[0]];
            unset($this->tables[$tableNames[0]]);
            $tableNames[0] = lcfirst($this->apiName);
        }
        ksort($this->tables);
        $vars = [
            'className'   => lcfirst($this->apiName),
            'tables'      => $this->tables,
            'sectionName' => lcfirst($this->sectionName)
        ];
        // Add create or replace view.
        if (!in_array(strtolower($this->apiName), array_map('strtolower', $tableNames), true)) {
            $vars['addView'] = ['tableName' => $tableNames[0], 'columns' => $this->tables[$tableNames[0]]['columns']];
        }
        try {
            $contents = $this->getTwig()
                ->render($this->getSqlTemplateName($data, $this->getPlatform()), $vars);
        } catch (Twig_Error $exp) {
            $this->getYem()
                ->triggerLogEvent(
                    'Yapeal.Log.log',
                    Logger::WARNING,
                    $this->getFailedToWriteFile($data, $eventName, $outputFile)
                );
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log', Logger::ERROR, 'Twig error', ['exception' => $exp]);
            return $event;
        }
        if (false === $this->saveToFile($outputFile, $contents)) {
            $this->getYem()
                ->triggerLogEvent(
                    $eventName,
                    Logger::WARNING,
                    $this->getFailedToWriteFile($data, $eventName, $outputFile)
                );
            return $event;
        }
        return $event->setHandledSufficiently();
    }
    /**
     * @param string $platform
     *
     * @return self Fluent interface.
     */
    public function setPlatform($platform)
    {
        $this->platform = $platform;
        return $this;
    }
    /**
     * @return string
     */
    protected function getPlatform()
    {
        return $this->platform;
    }
    /**
     * @param string[] $keyNames
     *
     * @return string
     */
    protected function getSqlKeys(array $keyNames = [])
    {
        if ($this->hasOwner()) {
            array_unshift($keyNames, 'ownerID');
        }
        return array_unique($keyNames);
    }
    /**
     * @param EveApiReadWriteInterface $data
     * @param string                   $platform
     * @param string                   $suffix
     *
     * @return string
     * @throws YapealException
     * @throws \LogicException
     */
    protected function getSqlTemplateName(EveApiReadWriteInterface $data, $platform = 'MySql', $suffix = 'twig')
    {
        // Section/Api.Platform.Suffix, Section/Api.Suffix, Section/Platform.Suffix,
        // Api.Platform.Suffix, Api.Suffix, Platform.Suffix, sql.Suffix
        $names = explode(
            ',',
            sprintf(
                '%1$s/%2$s.%3$s.%4$s,%1$s/%2$s.%4$s,%1$s/%3$s.%4$s,' . '%2$s.%3$s.%4$s,%2$s.%4$s,%3$s.%4$s,sql.%4$s',
                ucfirst($data->getEveApiSectionName()),
                $data->getEveApiName(),
                $platform,
                $suffix
            )
        );
        foreach ($names as $fileName) {
            if (is_file($this->getDir() . $fileName)) {
                return $fileName;
            }
        }
        $mess = sprintf(
            'Failed to find usable sql template file for EveApi %1$s/%2$s with platform of %3$s',
            ucfirst($data->getEveApiSectionName()),
            $data->getEveApiName(),
            $platform
        );
        throw new YapealException($mess);
    }
    /**
     * Used to determine if API is in section that has an owner.
     *
     * @return bool
     */
    protected function hasOwner()
    {
        return in_array(strtolower($this->sectionName), ['account', 'char', 'corp'], true);
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
        if ('ID' === substr($name, -2)) {
            return 'BIGINT(20) UNSIGNED NOT NULL';
        }
        $name = strtolower($name);
        foreach ([
                     'descr'          => 'TEXT NOT NULL',
                     'name'           => 'CHAR(100) NOT NULL',
                     'balance'        => 'DECIMAL(17, 2) NOT NULL',
                     'isk'            => 'DECIMAL(17, 2) NOT NULL',
                     'tax'            => 'DECIMAL(17, 2) NOT NULL',
                     'timeefficiency' => 'TINYINT(3) UNSIGNED NOT NULL',
                     'date'           => 'DATETIME NOT NULL DEFAULT \'1970-01-01 00:00:01\'',
                     'time'           => 'DATETIME NOT NULL DEFAULT \'1970-01-01 00:00:01\'',
                     'until'          => 'DATETIME NOT NULL DEFAULT \'1970-01-01 00:00:01\''
                 ] as $search => $replace) {
            if (false !== strpos($name, $search)) {
                return $replace;
            }
        }
        return $forValue ? 'TEXT NOT NULL' : 'VARCHAR(255) DEFAULT \'\'';
    }
    /**
     * @param SimpleXMLIterator $sxi
     * @param string            $xPath
     */
    protected function processRowset(SimpleXMLIterator $sxi, $xPath = '//result/rowset')
    {
        $items = $sxi->xpath($xPath);
        if (0 === count($items)) {
            return;
        }
        foreach ($items as $ele) {
            $tableName = ucfirst((string)$ele['name']);
            $colNames = explode(',', (string)$ele['columns']);
            $keyNames = explode(',', (string)$ele['key']);
            $columns = [];
            foreach ($keyNames as $keyName) {
                $columns[$keyName] = $this->inferTypeFromName($keyName);
            }
            foreach ($colNames as $colName) {
                $columns[$colName] = $this->inferTypeFromName($colName);
            }
            if ($this->hasOwner()) {
                $columns['ownerID'] = 'BIGINT(20) UNSIGNED NOT NULL';
            }
            ksort($columns);
            $this->tables[$tableName] = ['columns' => $columns, 'keys' => $this->getSqlKeys($keyNames)];
        }
    }
    /**
     * @param SimpleXMLIterator $sxi
     * @param string            $tableName
     * @param string            $xpath
     */
    protected function processValueOnly(SimpleXMLIterator $sxi, $tableName, $xpath = '//result/child::*[not(*|@*)]')
    {
        $items = $sxi->xpath($xpath);
        if (0 === count($items)) {
            return;
        }
        $columns = [];
        /**
         * @type SimpleXMLElement $ele
         */
        foreach ($items as $ele) {
            $name = (string)$ele->getName();
            $columns[$name] = $this->inferTypeFromName($name, true);
        }
        if ($this->hasOwner()) {
            $columns['ownerID'] = 'BIGINT(20) UNSIGNED NOT NULL';
        }
        ksort($columns);
        $this->tables[$tableName] = ['columns' => $columns];
        $keys = $this->getSqlKeys();
        if (0 !== count($keys)) {
            $this->tables[$tableName]['keys'] = $keys;
        }
    }
    /**
     * @type string $apiName
     */
    protected $apiName;
    /**
     * @type string $platform Sql connection platform being used.
     */
    protected $platform;
    /**
     * @type string $sectionName
     */
    protected $sectionName;
    /**
     * @type integer $tableCount
     */
    protected $tableCount = 0;
    /**
     * @type array $tables
     */
    protected $tables;
}
