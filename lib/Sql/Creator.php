<?php
declare(strict_types = 1);
/**
 * Contains Creator class.
 *
 * PHP version 7.0+
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal which can be used to access the Eve Online
 * API data and place it into a database.
 * Copyright (C) 2015-2016 Michael Cummings
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
 * <http://spdx.org/licenses/LGPL-3.0.html>.
 *
 * You should be able to find a copy of this license in the LICENSE.md file. A copy of the GNU GPL should also be
 * available in the GNU-GPL.md file.
 *
 * @copyright 2015-2016 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Sql;

use Yapeal\Console\Command\EveApiCreatorTrait;
use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\MediatorInterface;
use Yapeal\FileSystem\RelativeFileSearchTrait;
use Yapeal\Log\Logger;

/**
 * Class Creator
 */
class Creator
{
    use EveApiCreatorTrait, RelativeFileSearchTrait;
    /**
     * Creator constructor.
     *
     * @param \Twig_Environment $twig
     * @param string            $dir
     * @param string            $platform
     */
    public function __construct(\Twig_Environment $twig, string $dir = __DIR__, $platform = 'MySql')
    {
        $this->setRelativeBaseDir($dir);
        $this->setPlatform($platform);
        $this->setTwig($twig);
        $this->twigExtension = strtolower($platform) . '.sql.twig';
    }
    /**
     * @param EveApiEventInterface $event
     * @param string               $eventName
     * @param MediatorInterface    $yem
     *
     * @return EveApiEventInterface
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function createSql(
        EveApiEventInterface $event,
        string $eventName,
        MediatorInterface $yem
    ): EveApiEventInterface
    {
        $this->setYem($yem);
        $data = $event->getData();
        $yem->triggerLogEvent('Yapeal.Log.log',
            Logger::DEBUG,
            $this->getReceivedEventMessage($data, $eventName, __CLASS__));
        // Only work with raw unaltered XML data.
        if (false !== strpos($data->getEveApiXml(), '<?yapeal.parameters.json')) {
            return $event->setHandledSufficiently();
        }
        $this->sectionName = $data->getEveApiSectionName();
        $this->apiName = $data->getEveApiName();
        $outputFile = sprintf('%1$s%2$s/Create%3$s.%4$s.sql',
            $this->getRelativeBaseDir(),
            ucfirst($this->sectionName),
            ucfirst($this->apiName),
            $this->getPlatform()
        );
        // Nothing to do if NOT overwriting and file exists.
        if (false === $this->isOverwrite() && is_file($outputFile)) {
            return $event->setHandledSufficiently();
        }
        $sxi = new \SimpleXMLIterator($data->getEveApiXml());
        $this->tables = [];
        $this->processValueOnly($sxi, $this->apiName);
        $this->processRowset($sxi, $this->apiName);
        $tCount = count($this->tables);
        if (0 === $tCount) {
            $mess = 'No SQL tables to create for';
            $yem->triggerLogEvent('Yapeal.Log.log', Logger::NOTICE, $this->createEveApiMessage($mess, $data));
            return $event->setHandledSufficiently();
        }
        ksort($this->tables);
        list($mSec, $sec) = explode(' ', microtime());
        $context = [
            'className' => lcfirst($this->apiName),
            'tables' => $this->tables,
            'sectionName' => lcfirst($this->sectionName),
            'version' => gmdate('YmdHis', $sec) . substr($mSec, 1, 4)
        ];
        $contents = $this->getContentsFromTwig($eventName, $data, $context);
        if (false === $contents) {
            return $event;
        }
        if (false === $this->safeFileWrite($contents, $outputFile, $this->getYem())) {
            $yem->triggerLogEvent($eventName,
                    Logger::WARNING,
                    $this->getFailedToWriteFileMessage($data, $eventName, $outputFile));
            return $event;
        }
        return $event->setHandledSufficiently();
    }
    /**
     * @param string $platform
     *
     * @return self Fluent interface.
     */
    public function setPlatform(string $platform)
    {
        $this->platform = $platform;
        return $this;
    }
    /**
     * @param \SimpleXMLIterator $sxi
     * @param string             $apiName
     * @param string             $xPath
     */
    protected function processRowset(\SimpleXMLIterator $sxi, string $apiName, string $xPath = '//result/rowset')
    {
        $items = $sxi->xpath($xPath);
        if (0 === count($items)) {
            return;
        }
        foreach ($items as $ele) {
            $rsName = ucfirst((string)$ele['name']);
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
            uksort($columns,
                function ($alpha, $beta) {
                    return strtolower($alpha) <=> strtolower($beta);
                });
            if (0 === count($this->tables)) {
                $this->tables[$apiName] = ['columns' => $columns, 'keys' => $this->getSqlKeys($keyNames)];
            } else {
                $this->tables[$rsName] = ['columns' => $columns, 'keys' => $this->getSqlKeys($keyNames)];
            }
        }
    }
    /**
     * @param \SimpleXMLIterator $sxi
     * @param string             $tableName
     * @param string             $xpath
     */
    protected function processValueOnly(
        \SimpleXMLIterator $sxi,
        string $tableName,
        string $xpath = '//result/child::*[not(*|@*|self::dataTime)]'
    ) {
        $items = $sxi->xpath($xpath);
        if (0 === count($items)) {
            return;
        }
        $columns = [];
        foreach ($items as $ele) {
            $name = (string)$ele->getName();
            $columns[$name] = $this->inferTypeFromName($name, true);
        }
        if ($this->hasOwner()) {
            $columns['ownerID'] = 'BIGINT(20) UNSIGNED NOT NULL';
        }
        uksort($columns,
            function ($alpha, $beta) {
                return strtolower($alpha) <=> strtolower($beta);
            });
        $keys = $this->getSqlKeys();
        if (0 !== count($keys)) {
            $this->tables[$tableName] = ['columns' => $columns, 'keys' => $keys];
        } else {
            $this->tables[$tableName] = ['columns' => $columns];
        }
    }
    /**
     * @return string
     */
    private function getPlatform(): string
    {
        return $this->platform;
    }
    /**
     * @param string[] $keyNames
     *
     * @return string[]
     */
    private function getSqlKeys(array $keyNames = []): array
    {
        if ($this->hasOwner()) {
            array_unshift($keyNames, 'ownerID');
        }
        return array_unique($keyNames);
    }
    /**
     * Used to determine if API is in section that has an owner.
     *
     * @return bool
     */
    private function hasOwner(): bool
    {
        return in_array(strtolower($this->sectionName), ['account', 'char', 'corp'], true);
    }
    /**
     * Used to infer(choose) type from element or attribute's name.
     *
     * @param string $name Name of the element or attribute.
     * @param bool   $forValue Determines if returned type is going to be used for element or an attribute.
     *
     * @return string Returns the inferred type from the name.
     */
    private function inferTypeFromName(string $name, bool $forValue = false): string
    {
        if ('ID' === substr($name, -2)) {
            return 'BIGINT(20) UNSIGNED NOT NULL';
        }
        $name = strtolower($name);
        foreach ([
                     'descr' => 'TEXT NOT NULL',
                     'name' => 'CHAR(100) NOT NULL',
                     'balance' => 'DECIMAL(17, 2) NOT NULL',
                     'isk' => 'DECIMAL(17, 2) NOT NULL',
                     'tax' => 'DECIMAL(17, 2) NOT NULL',
                     'timeefficiency' => 'TINYINT(3) UNSIGNED NOT NULL',
                     'date' => 'DATETIME NOT NULL DEFAULT \'1970-01-01 00:00:01\'',
                     'time' => 'DATETIME NOT NULL DEFAULT \'1970-01-01 00:00:01\'',
                     'until' => 'DATETIME NOT NULL DEFAULT \'1970-01-01 00:00:01\'',
                     'errorcode' => 'SMALLINT(4) UNSIGNED NOT NULL',
                     'level' => 'SMALLINT(4) UNSIGNED NOT NULL',
                     'logoncount' => 'BIGINT(20) UNSIGNED NOT NULL',
                     'logonminutes' => 'BIGINT(20) UNSIGNED NOT NULL',
                     'trainingend' => 'DATETIME NOT NULL DEFAULT \'1970-01-01 00:00:01\'',
                     'skillintraining' => 'BIGINT(20) UNSIGNED NOT NULL',
                     'trainingdestinationsp' => 'BIGINT(20) UNSIGNED NOT NULL',
                     'trainingstartsp' => 'BIGINT(20) UNSIGNED NOT NULL'
                 ] as $search => $replace) {
            if (false !== strpos($name, $search)) {
                return $replace;
            }
        }
        return $forValue ? 'TEXT NOT NULL' : 'VARCHAR(255) DEFAULT \'\'';
    }
    /**
     * @var string $apiName
     */
    private $apiName;
    /**
     * @var string $platform Sql connection platform being used.
     */
    private $platform;
    /**
     * @var string $sectionName
     */
    private $sectionName;
    /**
     * @var array $tables
     */
    private $tables;
    /**
     * @var string $twigExtension
     */
    protected $twigExtension = 'sql.twig';
}
