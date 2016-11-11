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
use Yapeal\Exception\YapealFileSystemException;
use Yapeal\FileSystem\RelativeFileSearchTrait;
use Yapeal\FileSystem\SafeFileHandlingTrait;
use Yapeal\Log\Logger;
use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Class Transformer
 */
class Transformer implements TransformerInterface
{
    use EveApiEventEmitterTrait;
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
     */
    public function transformEveApi(EveApiEventInterface $event, string $eventName, MediatorInterface $yem)
    {
        $this->setYem($yem);
        $data = $event->getData();
        $yem->triggerLogEvent('Yapeal.Log.log',
            Logger::DEBUG,
            $this->getReceivedEventMessage($data, $eventName, __CLASS__));
        $xml = $this->addYapealProcessingInstructionToXml($data)
            ->performTransform($data);
        if (false === $xml) {
            $mess = 'Failed to transform the XML of';
            $yem->triggerLogEvent('Yapeal.Log.log',
                Logger::WARNING,
                $this->createEventMessage($mess, $data, $eventName));
            return $event;
        }
        $data->setEveApiXml($this->getTidy()
            ->repairString($xml));
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
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    private function addYapealProcessingInstructionToXml(EveApiReadWriteInterface $data): self
    {
        $xml = $data->getEveApiXml();
        if (false === $xml || '' === $xml) {
            return $this;
        }
        $arguments = $data->getEveApiArguments();
        // Include only partial vCode for security.
        if (!empty($arguments['vCode'])) {
            $arguments['vCode'] = substr($arguments['vCode'], 0, min(8, strlen($arguments['vCode']) - 1)) . '...';
        }
        // Remove arguments that should never be included.
        unset($arguments['mask'], $arguments['rowCount']);
        ksort($arguments);
        $json = json_encode($arguments);
        $xml = str_replace(["encoding='UTF-8'?>\r\n<eveapi", "encoding='UTF-8'?>\n<eveapi"],
            [
                "encoding='UTF-8'?>\r\n<?yapeal.parameters.json " . $json . "?>\r\n<eveapi",
                "encoding='UTF-8'?>\n<?yapeal.parameters.json " . $json . "?>\n<eveapi"
            ],
            $xml);
        $data->setEveApiXml($xml);
        return $this;
    }
    /**
     * Checks for any libxml errors and logs them.
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    private function checkLibXmlErrors()
    {
        $errors = libxml_get_errors();
        if (0 !== count($errors)) {
            foreach ($errors as $error) {
                $this->getYem()
                    ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $error->message);
            }
        }
    }
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @return \SimpleXMLElement|false
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    private function getStyleSheetInstance(EveApiReadWriteInterface $data)
    {
        try {
            $xslFile = $this->findRelativeFileWithPath(ucfirst($data->getEveApiSectionName()),
                $data->getEveApiName(),
                'xsl');
        } catch (YapealFileSystemException $exc) {
            $mess = 'Failed to find accessible XSL file during the transform of';
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log',
                    Logger::DEBUG,
                    $this->createEveApiMessage($mess, $data),
                    ['exception' => $exc]);
            return false;
        }
        $styleSheet = $this->safeFileRead($xslFile);
        if (false === $styleSheet) {
            $mess = sprintf('Failed to read XSL file %s during the transform of', $xslFile);
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $this->createEveApiMessage($mess, $data));
            return false;
        }
        if ('' === $styleSheet) {
            $mess = sprintf('Received an empty XSL file %s during the transform of', $xslFile);
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $this->createEveApiMessage($mess, $data));
            return false;
        }
        try {
            $instance = new \SimpleXMLElement($styleSheet);
        } catch (\Exception $exc) {
            $mess = sprintf('SimpleXMLElement exception caused by XSL file %s during the transform of', $xslFile);
            $this->getYem()
                ->triggerLogEvent('Yapeal.log.log',
                    Logger::DEBUG,
                    $this->createEveApiMessage($mess, $data),
                    ['exception' => $exc]);
            $this->checkLibXmlErrors();
            return false;
        }
        $mess = sprintf('Transforming XML using XSL file %s', $xslFile);
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $this->createEveApiMessage($mess, $data));
        return $instance;
    }
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
                'wrap' => '250'
            ];
            $this->tidy = new \tidy(null, $tidyConfig, 'utf8');
        }
        return $this->tidy;
    }
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @return \SimpleXMLElement|false
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    private function getXmlInstance(EveApiReadWriteInterface $data)
    {
        $xml = $data->getEveApiXml();
        if (false === $xml || '' === $xml) {
            $mess = 'Given empty XML during the transform of';
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $this->createEveApiMessage($mess, $data));
            return false;
        }
        try {
            $instance = new \SimpleXMLElement($xml);
        } catch (\Exception $exc) {
            $mess = 'The XML cause SimpleXMLElement exception during the transform of';
            $this->getYem()
                ->triggerLogEvent('Yapeal.log.log',
                    Logger::WARNING,
                    $this->createEveApiMessage($mess, $data),
                    ['exception' => $exc]);
            $apiName = $data->getEveApiName();
            $data->setEveApiName('Untransformed_' . $apiName);
            // Cache error causing XML.
            $this->emitEvents($data, 'preserve', 'Yapeal.Xml.Error');
            $data->setEveApiName($apiName);
            $this->checkLibXmlErrors();
            return false;
        }
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
     * @return false|string
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    private function performTransform(EveApiReadWriteInterface $data)
    {
        libxml_clear_errors();
        libxml_use_internal_errors(true);
        $styleInstance = $this->getStyleSheetInstance($data);
        if (false === $styleInstance) {
            libxml_use_internal_errors(false);
            return false;
        }
        $xslt = $this->getXslt();
        $result = $xslt->importStylesheet($styleInstance);
        if (false === $result) {
            $mess = 'XSLT could not import style sheet during the transform of';
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $this->createEveApiMessage($mess, $data));
            $this->checkLibXmlErrors();
            libxml_use_internal_errors(false);
            return false;
        }
        $xmlInstance = $this->getXmlInstance($data);
        if (false === $xmlInstance) {
            libxml_use_internal_errors(false);
            return false;
        }
        $result = $xslt->transformToXml($xmlInstance);
        if (false === $result) {
            $this->checkLibXmlErrors();
            $apiName = $data->getEveApiName();
            $data->setEveApiName('Untransformed_' . $apiName);
            // Cache error causing XML.
            $this->emitEvents($data, 'preserve', 'Yapeal.Xml.Error');
            $data->setEveApiName($apiName);
        }
        libxml_use_internal_errors(false);
        return $result;
    }
    /**
     * @var \tidy $tidy
     */
    private $tidy;
    /**
     * @var \XSLTProcessor $xslt
     */
    private $xslt;
}
