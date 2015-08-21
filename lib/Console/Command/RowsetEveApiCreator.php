<?php
/**
 * Contains RowsetEveApiCreator class.
 *
 * PHP version 5.5
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2015 Michael Cummings
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
 * <http://www.gnu.org/licenses/>.
 *
 * You should be able to find a copy of this license in the LICENSE.md file. A
 * copy of the GNU GPL should also be available in the GNU-GPL.md file.
 *
 * @copyright 2015 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Console\Command;

use FilePathNormalizer\FilePathNormalizerTrait;
use SimpleXMLElement;
use SimpleXMLIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use tidy;
use Yapeal\Configuration\ConsoleWiring;
use Yapeal\Configuration\WiringInterface;
use Yapeal\Console\CommandToolsTrait;
use Yapeal\Container\ContainerInterface;
use Yapeal\Event\EveApiEventEmitterTrait;
use Yapeal\Event\EventMediatorInterface;
use Yapeal\Exception\YapealDatabaseException;
use Yapeal\Exception\YapealException;
use Yapeal\Xml\EveApiReadWriteInterface;
use Yapeal\Xml\EveApiXmlData;

/**
 * Class RowsetEveApiCreator
 */
class RowsetEveApiCreator extends Command implements WiringInterface
{
    use CommandToolsTrait, EveApiEventEmitterTrait, FilePathNormalizerTrait;
    /**
     * @param string|null $name
     * @param string $cwd
     * @param ContainerInterface $dic
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function __construct($name, $cwd, ContainerInterface $dic)
    {
        $this->setDescription(
            'Retrieves Eve Api XML from servers and creates class, xsd, sql files for simple rowsets'
        );
        $this->setName($name);
        $this->setCwd($cwd);
        $this->setDic($dic);
        parent::__construct($name);
    }
    protected function analyzeEveApi(EveApiReadWriteInterface $data)
    {
        $xml = <<<'XML'
<?xml version='1.0' encoding='UTF-8'?>
<eveapi version="2">
  <currentTime>2010-02-26 05:34:14</currentTime>
  <result>
    <corporationID>150212025</corporationID>
    <corporationName>Banana Republic</corporationName>
    <ticker>BR</ticker>
    <ceoID>150208955</ceoID>
    <ceoName>Mark Roled</ceoName>
    <stationID>60003469</stationID>
    <stationName>Jita IV - Caldari Business Tribunal Information Center</stationName>
    <description>Garth's testing corp of awesome sauce, win sauce as it were. In this corp...&lt;br&gt;&lt;br&gt;IT HAPPENS ALL OVER</description>
    <url>some url</url>
    <allianceID>150430947</allianceID>
    <allianceName>The Dead Rabbits</allianceName>
    <taxRate>93.7</taxRate>
    <memberCount>3</memberCount>
    <memberLimit>6300</memberLimit>
    <shares>1</shares>
    <rowset name="divisions" key="accountKey" columns="accountKey,description">
      <row accountKey="1000" description="Division 1"/>
      <row accountKey="1001" description="Division 2"/>
      <row accountKey="1002" description="Division 3"/>
      <row accountKey="1003" description="Division 4"/>
      <row accountKey="1004" description="Division 5"/>
      <row accountKey="1005" description="Division 6"/>
      <row accountKey="1006" description="Division 7"/>
    </rowset>
    <rowset name="walletDivisions" key="accountKey" columns="accountKey,description">
      <row accountKey="1000" description="Wallet Division 1"/>
      <row accountKey="1001" description="Wallet Division 2"/>
      <row accountKey="1002" description="Wallet Division 3"/>
      <row accountKey="1003" description="Wallet Division 4"/>
      <row accountKey="1004" description="Wallet Division 5"/>
      <row accountKey="1005" description="Wallet Division 6"/>
      <row accountKey="1006" description="Wallet Division 7"/>
    </rowset>
    <logo>
      <graphicID>0</graphicID>
      <shape1>448</shape1>
      <shape2>0</shape2>
      <shape3>418</shape3>
      <color1>681</color1>
      <color2>676</color2>
      <color3>0</color3>
    </logo>
    <key accessMask="268435455" type="Account" expires="2015-06-09 07:17:53">
      <rowset name="characters" key="characterID" columns="characterID,characterName,corporationID,corporationName,allianceID,allianceName,factionID,factionName">
        <row characterID="92882511" characterName="Mann im Mond" corporationID="1000006" corporationName="Deep Core Mining Inc." allianceID="0" allianceName="" factionID="0" factionName="" />
      </rowset>
    </key>
  </result>
  <cachedUntil>2010-02-26 11:34:14</cachedUntil>
</eveapi>
XML;
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
            'indent' => true,
            'indent-spaces' => 4,
            'output-xml' => true,
            'input-xml' => true,
            'wrap' => '120'
        ];
        $xml = (new tidy())->repairString($output, $tidyConfig, 'utf8');
        file_put_contents($xsdFile, $xml);
    }
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->addArgument(
            'section_name',
            InputArgument::REQUIRED,
            'Name of Eve Api section to retrieve.'
        );
        $this->addArgument(
            'api_name',
            InputArgument::REQUIRED,
            'Name of Eve Api to retrieve.'
        );
        $this->addArgument(
            'mask',
            InputArgument::REQUIRED,
            'Bit mask for Eve Api.'
        );
        $this->addArgument(
            'post',
            InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
            'Optional list of additional POST parameter(s) to send to server.',
            []
        );
        $help = <<<EOF
The <info>%command.full_name%</info> command retrieves the XML data from the Eve Api
server and creates Yapeal Eve API Database class, xsd, and update sql files
for simple rowset type APIs.

    <info>php %command.full_name% section_name api_name mask [<post>]...</info>

EXAMPLES:
Create Char/AccountBalance class, xsd, and update sql files in lib/Database/.
    <info>%command.name% char AccountBalance 1 "keyID=1156" "vCode=abc123"</info>

EOF;
        $this->setHelp($help);
    }
    /**
     * Executes the current command.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return null|int null or 0 if everything went fine, or an error code
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Yapeal\Exception\YapealConsoleException
     * @throws \Yapeal\Exception\YapealDatabaseException
     * @throws \Yapeal\Exception\YapealException
     * @see    setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $posts = $this->processPost($input);
        $dic = $this->getDic();
        $this->wire($dic);
        $apiName = $input->getArgument('api_name');
        $sectionName = $input->getArgument('section_name');
        /**
         * @type EventMediatorInterface $yem
         */
        $this->yem = $dic['Yapeal.Event.EventMediator'];
        /**
         * Get new Data instance from factory.
         *
         * @type EveApiReadWriteInterface $data
         */
        /** @noinspection DisconnectedForeachInstructionInspection */
        $data = $dic['Yapeal.Xml.Data'];
        $data->setEveApiName($apiName)
            ->setEveApiSectionName($sectionName)
            ->setEveApiArguments($posts);
        foreach (['retrieve', 'transform', 'preserve'] as $eventName) {
            $this->emitEvents($data, $eventName);
        }
        if (false === $data->getEveApiXml()) {
            $mess = sprintf(
                '<error>Could NOT retrieve Eve Api data for %1$s/%2$s</error>',
                strtolower($sectionName),
                $apiName
            );
            $output->writeln($mess);
            return 2;
        }
        $subs = $this->getSubs($data, $input);
        foreach (['EveApi' => 'php', 'Sql' => 'sql', 'Xsd' => 'xsd'] as $dirName => $suffix) {
            $template = $this->getTemplate($suffix, $output);
            $contents = $this->processTemplate($subs, $template);
            $fileName = sprintf(
                '%1$s/lib/%2$s/%3$s/%4$s.%5$s',
                $this->getDic()['Yapeal.baseDir'],
                $dirName,
                ucfirst($sectionName),
                $apiName,
                $suffix
            );
            $this->saveToFile($fileName, $contents);
        }
        return 0;
    }
    /**
     * @param InputInterface $input
     *
     * @return array
     */
    protected function processPost(InputInterface $input)
    {
        /**
         * @type array $posts
         */
        $posts = (array)$input->getArgument('post');
        if (0 !== count($posts)) {
            $arguments = [];
            foreach ($posts as $post) {
                list($key, $value) = explode('=', $post);
                $arguments[$key] = $value;
            }
            $posts = $arguments;
            return $posts;
        }
        return $posts;
    }
    /**
     * @param ContainerInterface $dic
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws YapealException
     * @throws YapealDatabaseException
     */
    public function wire(ContainerInterface $dic)
    {
        (new ConsoleWiring($dic))->wireAll();
    }
    /**
     * @param EveApiReadWriteInterface $data
     * @param InputInterface $input
     *
     * @return array
     */
    protected function getSubs(EveApiReadWriteInterface $data, InputInterface $input)
    {
        list($columnNames, $keyNames, $rowsetName) = $this->processXml($data);
        $apiName = ucfirst($input->getArgument('api_name'));
        $sectionName = $input->getArgument('section_name');
        $subs = [
            'className' => $apiName,
            'columnDefaults' => $this->getColumnDefaults($columnNames, $sectionName),
            'columnList' => $this->getColumnList($columnNames, $sectionName),
            'copyright' => gmdate('Y'),
            'getDelete' => $this->getDeleteFromTable($sectionName),
            'keys' => $this->getSqlKeys($keyNames, $sectionName),
            'mask' => $input->getArgument('mask'),
            'namespace' => $this->getNamespace($sectionName),
            'sectionName' => ucfirst($sectionName),
            'tableName' => lcfirst($sectionName) . $apiName,
            'rowAttributes' => $this->getRowAttributes($columnNames),
            'rowsetName' => $rowsetName,
            'updateName' => gmdate('YmdHi')
        ];
        return $subs;
    }
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @return array
     */
    protected function processXml(EveApiReadWriteInterface $data)
    {
        $simple = new SimpleXMLElement($data->getEveApiXml());
        $columnNames = (string)$simple->result[0]->rowset[0]['columns'];
        $columnNames = explode(',', $columnNames);
        $keyNames = (string)$simple->result[0]->rowset[0]['key'];
        $keyNames = explode(',', str_replace(' ', '', $keyNames));
        $rowsetName = (string)$simple->result[0]->rowset[0]['name'];
        return [$columnNames, $keyNames, $rowsetName];
    }
    /**
     * @param string[] $columnNames
     * @param string $sectionName
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
        $maxWidth = strlen(
                array_reduce(
                    $columnNames,
                    function ($k, $v) {
                        return (strlen($k) > strlen($v)) ? $k : $v;
                    }
                )
            ) + 2;
        $columns = [];
        foreach ($columnNames as $name) {
            $lcName = strtolower($name);
            $column = 'VARCHAR(255) DEFAULT \'\'';
            if (false !== strpos($lcName, 'name')) {
                $column = 'CHAR(100)           NOT NULL';
            }
            if (false !== strpos($lcName, 'tax')) {
                $column = 'DECIMAL(17, 2)      NOT NULL';
            }
            if (false !== strpos($lcName, 'time') || false !== strpos($lcName, 'date')) {
                $column = 'DATETIME            NOT NULL';
            }
            if ('ID' === substr($name, -2)) {
                $column = 'BIGINT(20) UNSIGNED NOT NULL';
            }
            $columns[] = sprintf('%1$-' . $maxWidth . 's %2$s', '"' . $name . '"', $column);
        }
        return implode(",\n" . str_repeat(' ', 4), $columns);
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
     * @param string[] $keyNames
     * @param string $sectionName
     *
     * @return string
     */
    protected function getSqlKeys(array $keyNames, $sectionName)
    {
        if (in_array(strtolower($sectionName), ['char', 'corp', 'account'], true)) {
            array_unshift($keyNames, 'ownerID');
        }
        $keyNames = array_unique($keyNames);
        return '"' . implode('", "', $keyNames) . '"';
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
     * @param  string[] $columnNames
     *
     * @return string
     */
    protected function getRowAttributes(array $columnNames)
    {
        sort($columnNames);
        $columns = [];
        foreach ($columnNames as $name) {
            $lcName = strtolower($name);
            $type = 'xs:token';
            if (false !== strpos($lcName, 'name')) {
                $type = 'eveNameType';
            }
            if (false !== strpos($lcName, 'tax')) {
                $type = 'eveISKType';
            }
            if (false !== strpos($lcName, 'time') || false !== strpos($lcName, 'date')) {
                $type = 'eveNEDTType';
            }
            if ('ID' === substr($name, -2)) {
                $type = 'eveIDType';
            }
            $columns[] = sprintf('<xs:attribute type="%1$s" name="%2$s"/>', $type, $name);
        }
        return implode("\n" . str_repeat(' ', 16), $columns);
    }
    /**
     * @param string $for
     * @param OutputInterface $output
     *
     * @return false|string
     * @throws \InvalidArgumentException
     */
    protected function getTemplate($for, OutputInterface $output)
    {
        $templateName = sprintf('%1$s/rowset.%2$s.template', __DIR__, $for);
        $templateName =
            $this->getFpn()
                ->normalizeFile($templateName);
        if (!is_file($templateName)) {
            $mess = '<error>Could NOT find template file ' . $templateName . '</error>';
            $output->writeln($mess);
            return false;
        }
        $template = file_get_contents($templateName);
        if (false === $template) {
            $mess = '<error>Could NOT open template file ' . $templateName . '</error>';
            $output->writeln($mess);
            return false;
        }
        return $template;
    }
    /**
     * @param array $subs
     * @param string $template
     *
     * @return string
     */
    protected function processTemplate(array $subs, $template)
    {
        $keys = [];
        $replacements = [];
        foreach ($subs as $name => $value) {
            $keys[] = '{' . $name . '}';
            $replacements[] = $value;
        }
        return str_replace($keys, $replacements, $template);
    }
    /**
     * @param string $fileName
     * @param string $contents
     *
     * @return int
     */
    protected function saveToFile($fileName, $contents)
    {
        $fileName =
            $this->getFpn()
                ->normalizeFile($fileName);
        return file_put_contents($fileName, $contents);
    }
    /**
     * @param string $apiName
     * @param string $sectionName
     *
     * @return string
     */
    protected function getTableName($apiName, $sectionName)
    {
        return lcfirst($sectionName) . $apiName;
    }
    /**
     * @param string $apiName
     * @param string $sectionName
     * @param string[] $posts
     *
     * @return \Yapeal\Xml\EveApiReadWriteInterface
     */
    protected function getXmlData($apiName, $sectionName, $posts)
    {
        return new EveApiXmlData($apiName, $sectionName, $posts);
    }
    /**
     * @param string $name
     * @param bool $forValue
     *
     * @return string
     */
    protected function getXsdType($name, $forValue = false)
    {
        $lcName = strtolower($name);
        $type = $forValue ? 'xs:string' : 'xs:token';
        if (false !== strpos($lcName, 'name')) {
            $type = 'eveNameType';
        }
        if (false !== strpos($lcName, 'tax')) {
            $type = 'eveISKType';
        }
        if (false !== strpos($lcName, 'time') || false !== strpos($lcName, 'date')) {
            $type = 'eveNEDTType';
        }
        if ('ID' === substr($name, -2)) {
            $type = 'eveIDType';
        }
        return $type;
    }
    /**
     * @param SimpleXMLIterator $sxi
     * @return string
     */
    protected function processValueOnly(SimpleXMLIterator $sxi)
    {
        $elements = $sxi->xpath('//result/child::*[not(*|@*)]');
        if (0 === count($elements)) {
            return '';
        }
        $rows = [];
        /**
         * @type SimpleXMLElement $ele
         */
        foreach ($elements as $ele) {
            $rows[] = $ele->getName();
        }
        sort($rows);
        $xsd = '';
        foreach ($rows as $name) {
            $xsd .= sprintf('<xs:element type="%1$s" name="%2$s"/>', getXsdType($name, true), $name) . PHP_EOL;
        }
        return $xsd;
    }
    /**
     * @param SimpleXMLIterator $sxi
     * @return string
     */
    protected function processWithKidsAndNoAttributes(SimpleXMLIterator $sxi)
    {
        $elements = $sxi->xpath('//result/child::*[* and not(@*)]');
        if (0 === count($elements)) {
            return '';
        }
        $parentXsd = <<<'XSD'
<xs:element name="%1$s">
    <xs:complexType>
        <xs:sequence>
            %2$s
        </xs:sequence>
    </xs:complexType>
</xs:element>
XSD;
        $rows = [];
        foreach ($elements as $ele) {
            $name = (string)$ele->getName();
            $kids = $ele->children();
            $children = [];
            /**
             * @type SimpleXMLElement $child
             */
            foreach ($kids as $child) {
                $cName = (string)$child->getName();
                $children[$cName] = sprintf('<xs:element type="%1$s" name="%2$s"/>', getXsdType($cName, true), $cName);
            }
            $rows[$name] = sprintf($parentXsd, $name, implode("\n", $children));
        }
        ksort($rows);
        $xsd = implode("\n", $rows);
        return $xsd;
    }
    /**
     * @param SimpleXMLIterator $sxi
     * @return string
     */
    protected function processNonRowset(SimpleXMLIterator $sxi)
    {
        $elements = $sxi->xpath('//result/child::*[* and @* and not(@name|@key)]');
        if (0 === count($elements)) {
            return '';
        }
        $parentXsd = <<<'XSD'
<xs:element name="%1$s">
    <xs:complexType>
        <xs:sequence>
            %2$s
        </xs:sequence>
        %3$s
    </xs:complexType>
</xs:element>
XSD;
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
                $attributes[$aName] =
                    sprintf('<xs:attribute type="%1$s" name="%2$s" use="required"/>', getXsdType($aName), $aName);
            }
            ksort($attributes);
            $kids = $ele->children();
            $children = [];
            /**
             * @type SimpleXMLIterator $child
             */
            foreach ($kids as $child) {
                $cName = (string)$child->getName();
                if ('rowset' !== $cName) {
                    $children[$cName] = sprintf(
                        '<xs:element type="%1$s" name="%2$s" minOccurs="0" maxOccurs="1"/>',
                        getXsdType($cName, true),
                        $cName
                    );
                } else {
                    $cName = (string)$child['name'];
                    $xPath = sprintf('//result/%1$s/rowset[@name=\'%2$s\']', $name, $cName);
                    $children[$cName] = processRowset($sxi, $xPath);
                }
            }
            ksort($children);
            $rows[$name] = sprintf($parentXsd, $name, implode("\n", $children), implode("\n", $attributes));
        }
        ksort($rows);
        $xsd = implode("\n", $rows);
        return $xsd;
    }
    /**
     * @param SimpleXMLIterator $sxi
     * @param string $xPath
     * @return string
     */
    protected function processRowset(SimpleXMLIterator $sxi, $xPath = '//result/rowset')
    {
        $elements = $sxi->xpath($xPath);
        if (0 === count($elements)) {
            return '';
        }
        $rowXsd = <<<'XSD'
<xs:complexType>
    <xs:simpleContent>
        <xs:extension base="xs:string">
            %2$s
        </xs:extension>
    </xs:simpleContent>
</xs:complexType>
XSD;
        $rowsets = [];
        $rowTypes = [];
        foreach ($elements as $ele) {
            $rowsets[] = (string)$ele['name'];
            $columns = explode(',', (string)$ele['columns']);
            sort($columns);
            $attributes = [];
            foreach ($columns as $cName) {
                $attributes[] =
                    sprintf('<xs:attribute type="%1$s" name="%2$s" use="required"/>', getXsdType($cName), $cName);
            }
            $rowTypes[(string)$ele['name']] = sprintf($rowXsd, (string)$ele['name'], implode("\n", $attributes));
        }
        sort($rowsets);
        $xsd = '';
        $elementXsd = <<<'XSD'
<xs:element name="%1$s" minOccurs="0" maxOccurs="1">
    <xs:complexType>
        <xs:sequence>
            <xs:element name="row" minOccurs="0" maxOccurs="unbounded">
                %2$s
            </xs:element>
        </xs:sequence>
        <xs:attributeGroup ref="rowsetAttrs"/>
    </xs:complexType>
</xs:element>
XSD;
        foreach ($rowsets as $name) {
            $xsd .= sprintf($elementXsd, $name, $rowTypes[$name]);
        }
        return $xsd;
    }
}
