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

use SimpleXMLElement;
use SimpleXMLIterator;
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
            '%1$s%2$s/%3$s.php',
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
            'className'    => ucfirst($data->getEveApiName()),
            'elementsVO'   => $this->processValueOnly($sxi),
            'elementsWKNA' => $this->processWithKidsAndNoAttributes($sxi),
            //'elementsNRS'  => $this->processNonRowset($sxi),
            'elementsNRS'  => [],
            'elementsRS'   => $this->processRowset($sxi),
            'mask'         => $data->getEveApiArgument('mask'),
            'namespace'    => 'Yapeal\\EveApi\\' . ucfirst($this->sectionName),
            'sectionName'  => ucfirst($this->sectionName),
            'tableName'    => lcfirst($this->sectionName) . ucfirst($data->getEveApiName())
        ];
        try {
            $contents = $this->getTwig()
                ->render('php.twig', $vars);
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
     * @return string
     */
    protected function getNamespace()
    {
        return 'Yapeal\EveApi\\' . ucfirst($this->sectionName);
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
                $attributes[$aName] = 'null';
            }
            ksort($attributes);
            $children = [];
            /**
             * @type SimpleXMLIterator $child
             */
            foreach ($ele->children() as $child) {
                $cName = (string)$child->getName();
                $children[$cName] = 'null';
            }
            ksort($children);
            $rows[$name] = ['children' => $children, 'attributes' => $attributes];
        }
        ksort($rows);
        return $rows;
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
            $name = ucfirst((string)$ele['name']);
            $columns = explode(',', (string)$ele['columns']);
            $children = [];
            foreach ($columns as $cName) {
                $children[$cName] = 'null';
            }
            if ($this->hasOwner()) {
                $children['ownerID'] = '$ownerID';
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
            $rows[$name] = 'null';
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
                $children[$cName] = 'null';
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
