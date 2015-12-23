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
namespace Yapeal\Xsd;

use SimpleXMLElement;
use SimpleXMLIterator;
use tidy;
use Twig_Environment;
use Yapeal\Console\Command\EveApiCreatorTrait;
use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\EventMediatorInterface;
use Yapeal\Log\Logger;

/**
 * Class Creator
 */
class Creator
{
    use EveApiCreatorTrait;
    /**
     * Creator constructor.
     *
     * @param string           $dir
     * @param Twig_Environment $twig
     */
    public function __construct($dir = __DIR__, Twig_Environment $twig)
    {
        $this->setDir($dir);
        $this->setTwig($twig);
    }
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
    public function createXsd(EveApiEventInterface $event, $eventName, EventMediatorInterface $yem)
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
            '%1$s%2$s/%3$s.xsd',
            $this->getDir(),
            $data->getEveApiSectionName(),
            $data->getEveApiName()
        );
        // Nothing to do if NOT overwriting and file exists.
        if (false === $this->isOverwrite() && is_file($outputFile)) {
            return $event;
        }
        $this->sectionName = $data->getEveApiSectionName();
        $xml = $data->getEveApiXml();
        $sxi = new SimpleXMLIterator($xml);
        $vars = [
            'elementsVO'   => $this->processValueOnly($sxi),
            'elementsWKNA' => $this->processWithKidsAndNoAttributes($sxi),
            //'elementsNRS'  => $this->processNonRowset($sxi),
            'elementsNRS'  => [],
            'elementsRS'   => $this->processRowset($sxi)
        ];
        try {
            $contents = $this->getTwig()
                ->render('xsd.twig', $vars);
        } catch (\Twig_Error $exp) {
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log', Logger::ERROR, 'Twig error', ['exception' => $exp]);
            $this->getYem()
                ->triggerLogEvent(
                    'Yapeal.Log.log',
                    Logger::WARNING,
                    $this->getFailedToWriteFile($data, $eventName, $outputFile)
                );
            return $event;
        }
        $tidyConfig = [
            'indent'        => true,
            'indent-spaces' => 4,
            'output-xml'    => true,
            'input-xml'     => true,
            'wrap'          => '120'
        ];
        $contents = (new tidy())->repairString($contents, $tidyConfig, 'utf8');
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
            return 'eveIDType';
        }
        $lcName = strtolower($name);
        $type = $forValue ? 'xs:string' : 'xs:token';
        foreach ([
                     'descr'          => 'xs:string',
                     'name'           => 'eveNameType',
                     'tax'            => 'eveISKType',
                     'balance'        => 'eveISKType',
                     'time'           => 'eveNEDTType',
                     'timeefficiency' => 'xs:unsignedByte',
                     'date'           => 'eveNEDTType'
                 ] as $search => $replace) {
            if (false !== strpos($lcName, $search)) {
                $type = $replace;
            }
        }
        return $type;
    }
    /**
     * @param SimpleXMLIterator $sxi
     *
     * @return array
     */
    protected function processNonRowsetWithSimpleChildren(SimpleXMLIterator $sxi)
    {
        $elements = $sxi->xpath('//result/child::*[@* and not(@name|@key) and child::*[not(*|@*)]]');
        if (0 === count($elements)) {
            return [];
        }
        $rows = [];
        /**
         * @type SimpleXMLIterator $ele
         */
        foreach ($elements as $ele) {
            $name = (string)$ele->getName();
            $columns = $ele->attributes();
            $attributes = [];
            /**
             * @type SimpleXMLElement $attr
             */
            foreach ($columns as $attr) {
                $aName = (string)$attr->getName();
                $attributes[$aName] = $this->inferTypeFromName($aName);
            }
            ksort($attributes);
            $children = [];
            /**
             * @type SimpleXMLIterator $child
             */
            foreach ($ele->children() as $child) {
                $cName = (string)$child->getName();
                $children[$cName] = $this->inferTypeFromName($cName, true);
            }
            ksort($children);
            $rows[$name] = ['children' => $children, 'attributes' => $attributes];
        }
        ksort($rows);
        $xsd = implode("\n", $rows);
        return $xsd;
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
        $rows = [];
        foreach ($elements as $ele) {
            $name = (string)$ele['name'];
            $columns = explode(',', (string)$ele['columns']);
            $children = [];
            foreach ($columns as $cName) {
                $children[$cName] = $this->inferTypeFromName($cName, true);
            }
            ksort($children);
            $rows[$name] = $children;
        }
        ksort($rows);
        return $rows;
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
        foreach ($elements as $ele) {
            $name = (string)$ele->getName();
            $children = [];
            /**
             * @type SimpleXMLElement $child
             */
            foreach ($ele->children() as $child) {
                $cName = (string)$child->getName();
                $children[$cName] = $this->inferTypeFromName($cName, true);
            }
            ksort($children);
            $rows[$name] = $children;
        }
        ksort($rows);
        return $rows;
    }
    /**
     * @type string $sectionName
     */
    protected $sectionName;
}
