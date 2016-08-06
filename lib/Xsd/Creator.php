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
namespace Yapeal\Xsd;

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
     */
    public function __construct(\Twig_Environment $twig, string $dir = __DIR__)
    {
        $this->setRelativeBaseDir($dir);
        $this->setTwig($twig);
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
    public function createXsd(
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
        $outputFile = sprintf('%1$s%2$s/%3$s.xsd',
            $this->getRelativeBaseDir(),
            ucfirst($this->sectionName),
            ucfirst($this->apiName)
        );
        // Nothing to do if NOT overwriting and file exists.
        if (false === $this->isOverwrite() && is_file($outputFile)) {
            return $event;
        }
        $xml = $data->getEveApiXml();
        if (false === $xml) {
            return $event->setHandledSufficiently();
        }
        $sxi = new \SimpleXMLIterator($xml);
        $this->tables = [];
        $this->processValueOnly($sxi, lcfirst($this->apiName));
        $this->processRowset($sxi);
        list($mSec, $sec) = explode(' ', microtime());
        $context = [
            'className' => lcfirst($this->apiName),
            'tables' => $this->tables,
            'sectionName' => lcfirst($this->sectionName),
            'version' => gmdate('YmdHis', $sec) . sprintf('.%0-3s', floor($mSec * 1000))
        ];
        $contents = $this->getContentsFromTwig($eventName, $data, $context);
        if (false === $contents) {
            return $event;
        }
        $contents = $this->getTidy()
            ->repairString($contents);
        if (false === $this->safeFileWrite($contents, $outputFile, $this->getYem())) {
            $yem->triggerLogEvent($eventName,
                Logger::WARNING,
                $this->getFailedToWriteFileMessage($data, $eventName, $outputFile));
            return $event;
        }
        return $event->setHandledSufficiently();
    }
    /**
     * @param \SimpleXMLIterator $sxi
     * @param string             $xpath
     */
    protected function processRowset(\SimpleXMLIterator $sxi, string $xpath = '//result/rowset')
    {
        $items = $sxi->xpath($xpath);
        if (0 === count($items)) {
            return;
        }
        $tables = [];
        foreach ($items as $ele) {
            $tableName = (string)$ele['name'];
            /**
             * @var string[] $colNames
             */
            $colNames = explode(',', (string)$ele['columns']);
            /**
             * @var string[] $keyNames
             */
            $keyNames = explode(',', (string)$ele['key']);
            $columns = [];
            foreach ($keyNames as $keyName) {
                $columns[$keyName] = $this->inferTypeFromName($keyName);
            }
            foreach ($colNames as $colName) {
                $columns[$colName] = $this->inferTypeFromName($colName);
            }
            uksort($columns,
                function ($alpha, $beta) {
                    return strtolower($alpha) <=> strtolower($beta);
                });
            $tables[$tableName] = ['attributes' => $columns];
        }
        uksort($tables,
            function ($alpha, $beta) {
                return strtolower($alpha) <=> strtolower($beta);
            });
        $this->tables = array_merge($this->tables, $tables);
    }
    /**
     * @param \SimpleXMLIterator $sxi
     *
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
        /**
         * @var \SimpleXMLElement $ele
         */
        foreach ($items as $ele) {
            $name = (string)$ele->getName();
            $columns[$name] = $this->inferTypeFromName($name, true);
        }
        uksort($columns,
            function ($alpha, $beta) {
                return strtolower($alpha) <=> strtolower($beta);
            });
        $this->tables[$tableName] = ['values' => $columns];
    }
    /**
     * @var string $twigExtension
     */
    protected $twigExtension = 'xsd.twig';
    /**
     * @return \tidy
     */
    private function getTidy(): \tidy
    {
        if (null === $this->tidy) {
            $tidyConfig = [
                'indent' => true,
                'indent-spaces' => 4,
                'input-xml' => true,
                'newline' => 'LF',
                'output-xml' => true,
                'wrap' => '120'
            ];
            $this->tidy = new \tidy(null, $tidyConfig, 'utf8');
        }
        return $this->tidy;
    }
    /**
     * Used to infer(choose) type from element or attribute's name.
     *
     * @param string $name     Name of the element or attribute.
     * @param bool   $forValue Determines if returned type is going to be used for element or an attribute.
     *
     * @return string Returns the inferred type from the name.
     */
    private function inferTypeFromName(string $name, bool $forValue = false): string
    {
        if ('ID' === substr($name, -2)) {
            return 'eveIDType';
        }
        $name = strtolower($name);
        foreach ([
                     'descr' => 'xs:string',
                     'name' => 'eveNameType',
                     'balance' => 'eveISKType',
                     'isk' => 'eveISKType',
                     'tax' => 'eveISKType',
                     'timeefficiency' => 'xs:unsignedByte',
                     'date' => 'eveNEDTType',
                     'time' => 'eveNEDTType',
                     'until' => 'eveNEDTType',
                     'errorcode' => 'xs:unsignedShort',
                     'level' => 'xs:unsignedShort'
                 ] as $search => $replace) {
            if (false !== strpos($name, $search)) {
                return $replace;
            }
        }
        return $forValue ? 'xs:string' : 'xs:token';
    }
    /**
     * @var string $apiName
     */
    private $apiName;
    /**
     * @var string $sectionName
     */
    private $sectionName;
    /**
     * @var array $tables
     */
    private $tables;
    /**
     * @var \tidy $tidy
     */
    private $tidy;
}
