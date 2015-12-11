<?php
/**
 * Contains Creator class.
 *
 * PHP version 5.4
 *
 * @copyright 2015 Michael Cummings
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\EveApi;

use SimpleXMLIterator;
use Symfony\Component\Console\Input\InputInterface;
use tidy;
use Yapeal\Console\Command\EveApiCreatorTrait;
use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\EventMediatorInterface;
use Yapeal\Log\Logger;
use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Class Creator
 */
class Creator extends AbstractCommonEveApi
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
        $xml = $data->getEveApiXml();
        $this->getYem()
            ->triggerLogEvent(
                'Yapeal.Log.log',
                Logger::DEBUG,
                $this->getReceivedEventMessage($data, $eventName, __CLASS__)
            );
        $xsd = <<<'XSD'
<?xml version="1.0" encoding="UTF-8"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema" attributeFormDefault="unqualified" elementFormDefault="qualified">
    <xs:include schemaLocation="../common.xsd"/>
    <xs:complexType name="resultType">
        <xs:sequence>
            {elementsVO}
            {elementsWKNA}
            {elementsNRS}
            {elementsRS}
        </xs:sequence>
    </xs:complexType>
</xs:schema>
XSD;
        $xsdFile = __DIR__ . '/test.xsd';
        $sxi = new SimpleXMLIterator($xml);
        $output = str_replace(
            ['{elementsVO}', '{elementsWKNA}', '{elementsNRS}', '{elementsRS}'],
            [processValueOnly($sxi), processWithKidsAndNoAttributes($sxi), processNonRowset($sxi), processRowset($sxi)],
            $xsd
        );
        $tidyConfig = [
            'indent'        => true,
            'indent-spaces' => 4,
            'output-xml'    => true,
            'input-xml'     => true,
            'wrap'          => '120'
        ];
        $xml = (new tidy())->repairString($output, $tidyConfig, 'utf8');
        file_put_contents($xsdFile, $xml);
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
    public function startEveApi(EveApiEventInterface $event, $eventName, EventMediatorInterface $yem)
    {
        $this->setYem($yem);
        $data = $event->getData();
        $this->getYem()
            ->triggerLogEvent(
                'Yapeal.Log.log',
                Logger::DEBUG,
                $this->getReceivedEventMessage($data, $eventName, __CLASS__)
            );
        if (!$this->gotApiLock($data)) {
            return $event;
        }
        $eventSuffixes = ['retrieve', 'create', 'transform', 'validate', 'preserve'];
        foreach ($eventSuffixes as $eventSuffix) {
            if (false === $this->emitEvents($data, $eventSuffix)) {
                return $event;
            }
            if (false === $data->getEveApiXml()) {
                $this->getYem()
                    ->triggerLogEvent(
                        'Yapeal.Log.log',
                        Logger::NOTICE,
                        $this->getEmptyXmlDataMessage($data, $eventSuffix)
                    );
                return $event;
            }
        }
        $this->releaseApiLock($data);
        return $event->setHandledSufficiently();
    }
    /**
     * @param string[] $columnNames
     * @param string   $sectionName
     *
     * @return string
     */
    protected function getColumnDefaults($columnNames, $sectionName)
    {
        if (in_array(strtolower($sectionName), ['char', 'corp', 'account'], true)) {
            $columnNames[] = 'ownerID';
        }
        $columnNames = array_unique($columnNames);
        sort($columnNames);
        $columns = [];
        foreach ($columnNames as $name) {
            $column = '\'' . $name . '\' => null';
            if ('ownerID' === $name) {
                $column = '\'' . $name . '\' => $ownerID';
            }
            $columns[] = $column;
        }
        return implode(",\n" . str_repeat(' ', 12), $columns);
    }
    /**
     * @param string $sectionName
     *
     * @return string
     */
    protected function getDeleteFromTable($sectionName)
    {
        if (in_array(strtolower($sectionName), ['char', 'corp', 'account'], true)) {
            return 'getDeleteFromTableWithOwnerID($tableName, $ownerID)';
        }
        return 'getDeleteFromTable($tableName)';
    }
    /**
     * @param string $sectionName
     *
     * @return string
     */
    protected function getNamespace($sectionName)
    {
        return 'Yapeal\EveApi\\' . ucfirst($sectionName);
    }
    /**
     * @param EveApiReadWriteInterface $data
     * @param InputInterface           $input
     *
     * @return array
     */
    protected function getSubs(EveApiReadWriteInterface $data, InputInterface $input)
    {
        list($columnNames, $keyNames, $rowsetName) = $this->processXml($data);
        $apiName = ucfirst($input->getArgument('api_name'));
        $sectionName = $input->getArgument('section_name');
        $sxi = new SimpleXMLIterator($data->getEveApiXml());
        $subs = [
            'className'      => $apiName,
            'columnDefaults' => $this->getColumnDefaults($columnNames, $sectionName),
            'columnList'     => $this->getColumnList($columnNames, $sectionName),
            'copyright'      => gmdate('Y'),
            'elementsVO'     => $this->processValueOnly($sxi),
            'elementsWKNA'   => $this->processWithKidsAndNoAttributes($sxi),
            'elementsNRS'    => $this->processNonRowset($sxi),
            'elementsRS'     => $this->processRowset($sxi),
            'getDelete'      => $this->getDeleteFromTable($sectionName),
            'keys'           => $this->getSqlKeys($keyNames, $sectionName),
            'mask'           => $input->getArgument('mask'),
            'namespace'      => $this->getNamespace($sectionName),
            'sectionName'    => ucfirst($sectionName),
            'tableName'      => lcfirst($sectionName) . $apiName,
            'rowAttributes'  => $this->getRowAttributes($columnNames),
            'rowsetName'     => $rowsetName,
            'updateName'     => gmdate('YmdHi')
        ];
        return $subs;
    }
}
