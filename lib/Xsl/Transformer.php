<?php
declare(strict_types = 1);
/**
 * Contains Transformer class.
 *
 * PHP version 7.0+
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2015-2016 Michael Cummings
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
 * @copyright 2015-2016 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Xsl;

use Yapeal\Event\EveApiEventEmitterTrait;
use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\MediatorInterface;
use Yapeal\Event\YEMAwareInterface;
use Yapeal\Exception\YapealFileSystemException;
use Yapeal\FileSystem\RelativeFileSearchTrait;
use Yapeal\FileSystem\SafeFileHandlingTrait;
use Yapeal\Log\Logger;
use Yapeal\Xml\EveApiReadWriteInterface;
use Yapeal\Xml\LibXmlChecksTrait;

/**
 * Class Transformer
 */
class Transformer implements TransformerInterface, YEMAwareInterface
{
    use EveApiEventEmitterTrait;
    use LibXmlChecksTrait;
    use RelativeFileSearchTrait;
    use SafeFileHandlingTrait;
    /**
     * Transformer Constructor.
     *
     * @param string $dir Base directory where Eve API XSL files can be found.
     */
    public function __construct(string $dir = __DIR__)
    {
        $this->setRelativeBaseDir($dir . '/');
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
     * @throws \UnexpectedValueException
     */
    public function transformEveApi(EveApiEventInterface $event, string $eventName, MediatorInterface $yem)
    {
        $this->setYem($yem);
        $data = $event->getData();
        $yem->triggerLogEvent('Yapeal.Log.log',
            Logger::DEBUG,
            $this->getReceivedEventMessage($data, $eventName, __CLASS__));
        // Pretty up the XML to make other processing easier.
        $data->setEveApiXml(tidy_repair_string($data->getEveApiXml(), $this->tidyConfig, 'utf8'));
        $xml = $this->addYapealProcessingInstructionToXml($data)
            ->performTransform($data);
        if (false === $xml) {
            return $event;
        }
        // Pretty up the transformed XML.
        $data->setEveApiXml(tidy_repair_string($xml, $this->tidyConfig, 'utf8'));
        $messagePrefix = 'Successfully transformed the XML of';
        $yem->triggerLogEvent('Yapeal.Log.log', Logger::INFO, $this->createEveApiMessage($messagePrefix, $data));
        return $event->setHandledSufficiently();
    }
    /**
     * Adds Processing Instruction to XML containing json encoding of any post used during retrieve.
     *
     * NOTE: This use to be done directly in the network retriever but felt modifying the XML like that belonged in
     * transform instead.
     *
     * @param EveApiReadWriteInterface $data
     *
     * @return self Fluent interface.
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    private function addYapealProcessingInstructionToXml(EveApiReadWriteInterface $data): self
    {
        $xml = $data->getEveApiXml();
        if ('' === $xml) {
            return $this;
        }
        $arguments = $data->getEveApiArguments();
        // Include only partial vCode for security.
        if (!empty($arguments['vCode'])) {
            $arguments['vCode'] = substr($arguments['vCode'], 0, min(8, strlen($arguments['vCode']) - 1)) . '...';
        }
        // Remove arguments that never need to be included.
        unset($arguments['mask'], $arguments['rowCount']);
        ksort($arguments);
        /*
         * Ignoring untestable edge case of json_encode returning false. It would require $data to be broken in
         * some way that also breaks json_encode.
         */
        $json = json_encode($arguments);
        $xml = str_ireplace("=\"utf-8\"?>\n<eveapi",
            "=\"utf-8\"?>\n<?yapeal.parameters.json " . $json . "?>\n<eveapi",
            $xml);
        $data->setEveApiXml(tidy_repair_string($xml, $this->tidyConfig, 'utf8'));
        return $this;
    }
    /**
     * Handles loading, verifying, and return the correct XSL style sheet as a SimpleXMLElement instance.
     *
     * @param EveApiReadWriteInterface $data
     *
     * @return \SimpleXMLElement|false
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    private function getStyleSheetInstance(EveApiReadWriteInterface $data)
    {
        try {
            $xslFile = $this->findRelativeFileWithPath(ucfirst($data->getEveApiSectionName()),
                $data->getEveApiName(),
                'xsl');
        } catch (YapealFileSystemException $exc) {
            $messagePrefix = 'Failed to find accessible XSL file during the transform of';
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log',
                    Logger::WARNING,
                    $this->createEveApiMessage($messagePrefix, $data),
                    ['exception' => $exc]);
            return false;
        }
        $styleSheet = $this->safeFileRead($xslFile);
        if (false === $styleSheet) {
            $messagePrefix = sprintf('Failed to read XSL file %s during the transform of', $xslFile);
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log', Logger::WARNING, $this->createEveApiMessage($messagePrefix, $data));
            return false;
        }
        if ('' === $styleSheet) {
            $messagePrefix = sprintf('Received an empty XSL file %s during the transform of', $xslFile);
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log', Logger::WARNING, $this->createEveApiMessage($messagePrefix, $data));
            return false;
        }
        libxml_clear_errors();
        libxml_use_internal_errors(true);
        $instance = false;
        try {
            $instance = new \SimpleXMLElement($styleSheet);
        } catch (\Exception $exc) {
            $messagePrefix = sprintf('SimpleXMLElement exception caused by XSL file %s during the transform of',
                $xslFile);
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log',
                    Logger::WARNING,
                    $this->createEveApiMessage($messagePrefix, $data),
                    ['exception' => $exc]);
            $this->checkLibXmlErrors($data, $this->getYem());
        }
        libxml_use_internal_errors(false);
        if (false !== $instance) {
            $messagePrefix = sprintf('Using XSL file %s during the transform of', $xslFile);
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log', Logger::INFO, $this->createEveApiMessage($messagePrefix, $data));
        }
        return $instance;
    }
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @return false|\SimpleXMLElement
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    private function getXmlInstance(EveApiReadWriteInterface $data)
    {
        $xml = $data->getEveApiXml();
        if ('' === $xml) {
            $messagePrefix = 'Given empty XML during the transform of';
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log', Logger::WARNING, $this->createEveApiMessage($messagePrefix, $data));
            return false;
        }
        libxml_clear_errors();
        libxml_use_internal_errors(true);
        $instance = false;
        try {
            $instance = new \SimpleXMLElement($xml);
        } catch (\Exception $exc) {
            $messagePrefix = 'The XML cause SimpleXMLElement exception during the transform of';
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log',
                    Logger::WARNING,
                    $this->createEveApiMessage($messagePrefix, $data),
                    ['exception' => $exc]);
            // Cache error causing XML.
            $apiName = $data->getEveApiName();
            $data->setEveApiName('Untransformed_' . $apiName);
            $this->emitEvents($data, 'preserve', 'Yapeal.Xml.Error');
            $data->setEveApiName($apiName);
            // Empty invalid XML since it is not usable.
            $data->setEveApiXml('');
            $this->checkLibXmlErrors($data, $this->getYem());
        }
        libxml_use_internal_errors(false);
        return $instance;
    }
    /**
     * @return \XSLTProcessor
     */
    private function getXslt(): \XSLTProcessor
    {
        if (null === $this->xslt) {
            $this->xslt = new \XSLTProcessor();
        }
        return $this->xslt;
    }
    /**
     * Does actual XSL transform on the Eve API XML.
     *
     * @param EveApiReadWriteInterface $data
     *
     * @return string|false
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    private function performTransform(EveApiReadWriteInterface $data)
    {
        if (false === $styleInstance = $this->getStyleSheetInstance($data)) {
            return false;
        }
        if (false === $xmlInstance = $this->getXmlInstance($data)) {
            return false;
        }
        libxml_clear_errors();
        libxml_use_internal_errors(true);
        $xslt = $this->getXslt();
        if (false === $xslt->importStylesheet($styleInstance)) {
            $messagePrefix = 'XSLT could not import style sheet during the transform of';
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log', Logger::WARNING, $this->createEveApiMessage($messagePrefix, $data));
            $this->checkLibXmlErrors($data, $this->getYem());
            libxml_use_internal_errors(false);
            return false;
        }
        $xml = $xslt->transformToXml($xmlInstance);
        libxml_use_internal_errors(false);
        return $xml;
    }
    /**
     * @var array $tidyConfig
     */
    private $tidyConfig = [
        'indent' => true,
        'indent-spaces' => 4,
        'input-xml' => true,
        'newline' => 'LF',
        'output-xml' => true,
        'wrap' => '250'
    ];
    /**
     * @var \XSLTProcessor $xslt
     */
    private $xslt;
}
