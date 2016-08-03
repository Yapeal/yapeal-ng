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
use Yapeal\FileSystem\RelativeFileSearchTrait;
use Yapeal\Log\Logger;
use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Class Transformer
 */
class Transformer implements TransformerInterface
{
    use EveApiEventEmitterTrait, RelativeFileSearchTrait;
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
     * @return EveApiEventInterface|\EventMediator\EventInterface
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function transformEveApi(
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
        $fileName = $this->findEveApiFile($data->getEveApiSectionName(), $data->getEveApiName(), 'xsl');
        if ('' === $fileName) {
            return $event;
        }
        $xml = $this->addYapealProcessingInstructionToXml($data)
            ->performTransform($fileName, $data);
        if (false === $xml) {
            $mess = 'Failed to transform xml during';
            $yem->triggerLogEvent('Yapeal.Log.log',
                Logger::WARNING,
                $this->createEventMessage($mess, $data, $eventName));
            return $event;
        }
        $xml = (new \tidy())->repairString($xml, $this->tidyConfig, 'utf8');
        return $event->setData($data->setEveApiXml($xml))
            ->setHandledSufficiently();
    }
    /**
     * Adds Processing Instruction to XML containing json encoding of any post used during retrieve.
     *
     * NOTE: This use to be done directly in the network retriever but felt modifying the XML like that belonged in
     * transform instead.
     *
     * @param EveApiReadWriteInterface $data
     *
     * @return \Yapeal\Xsl\Transformer Fluent interface.
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    private function addYapealProcessingInstructionToXml(EveApiReadWriteInterface $data): self
    {
        $xml = $data->getEveApiXml();
        if (false === $xml) {
            return $this;
        }
        $arguments = $data->getEveApiArguments();
        if (!empty($arguments['vCode'])) {
            $arguments['vCode'] = substr($arguments['vCode'], 0, 8) . '...';
        }
        if (!in_array($data->getEveApiName(), ['accountBalance', 'walletJournal', 'walletTransactions'], true)) {
            unset($arguments['accountKey']);
        }
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
     * @return \DOMDocument
     */
    private function getDom(): \DOMDocument
    {
        if (null === $this->dom) {
            $this->dom = new \DOMDocument();
        }
        return $this->dom;
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
     * @param string                   $fileName
     * @param EveApiReadWriteInterface $data
     *
     * @return false|string
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    private function performTransform(string $fileName, EveApiReadWriteInterface $data)
    {
        libxml_clear_errors();
        libxml_use_internal_errors(true);
        libxml_clear_errors();
        $xslt = $this->getXslt();
        $dom = $this->getDom();
        $dom->load($fileName);
        $xslt->importStylesheet($dom);
        $result = false;
        try {
            $result = $xslt->transformToXml(new \SimpleXMLElement($data->getEveApiXml()));
        } catch (\Exception $exc) {
            $mess = 'XML cause SimpleXMLElement exception in';
            $this->getYem()
                ->triggerLogEvent('Yapeal.log.log',
                    Logger::WARNING,
                    $this->createEveApiMessage($mess, $data),
                    ['exception' => $exc]);
        }
        if (false === $result) {
            /**
             * @var array $errors
             */
            $errors = libxml_get_errors();
            if (0 !== count($errors)) {
                foreach ($errors as $error) {
                    $this->getYem()
                        ->triggerLogEvent('Yapeal.Log.log', Logger::NOTICE, $error->message);
                }
            }
            $apiName = $data->getEveApiName();
            $data->setEveApiName('Untransformed_' . $apiName);
            // Cache error causing XML.
            $this->emitEvents($data, 'preserve', 'Yapeal.Xml.Error');
            $data->setEveApiName($apiName);
        }
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        libxml_clear_errors();
        return $result;
    }
    /**
     * @var \DOMDocument $dom
     */
    private $dom;
    /**
     * Holds tidy config settings.
     *
     * @var array $tidyConfig
     */
    private $tidyConfig = [
        'indent' => true,
        'indent-spaces' => 4,
        'input-xml' => true,
        'newline' => 'LF',
        'output-xml' => true,
        'wrap' => '1000'
    ];
    /**
     * @var \XSLTProcessor $xslt
     */
    private $xslt;
}
