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
use Yapeal\Event\MediatorInterface;
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
     * @param Twig_Environment $twig
     * @param string           $dir
     */
    public function __construct(Twig_Environment $twig, $dir = __DIR__)
    {
        $this->setDir($dir);
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
    public function createEveApi(EveApiEventInterface $event, $eventName, MediatorInterface $yem)
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
        $sxi = new SimpleXMLIterator($data->getEveApiXml());
        $this->tables = [];
        $this->processValueOnly($sxi, ucfirst($this->sectionName) . $data->getEveApiName());
        $this->processRowset($sxi);
        ksort($this->tables);
        $vars = [
            'className'   => ucfirst($data->getEveApiName()),
            'tables'      => $this->tables,
            'hasOwner'    => $this->hasOwner(),
            'mask'        => $data->getEveApiArgument('mask'),
            'namespace'   => $this->getNamespace(),
            'sectionName' => $this->sectionName,
            'tableNames'  => array_keys($this->tables)
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
     * Used to infer(choose) default value from element or attribute's name.
     *
     * @param string $name Name of the element or attribute.
     *
     * @return string Returns the inferred value from the name.
     */
    protected function inferDefaultFromName($name)
    {
        $name = strtolower($name);
        $column = 'null';
        foreach ([
                     'descr'          => '\'\'',
                     'name'           => '\'\'',
                     'balance'        => '\'0.0\'',
                     'isk'            => '\'0.0\'',
                     'tax'            => '\'0.0\'',
                     'timeefficiency' => 'null',
                     'date'           => '\'1970-01-01 00:00:01\'',
                     'time'           => '\'1970-01-01 00:00:01\'',
                     'until'          => '\'1970-01-01 00:00:01\''
                 ] as $search => $replace) {
            if (false !== strpos($name, $search)) {
                return $replace;
            }
        }
        return $column;
    }
    /**
     * @param SimpleXMLIterator $sxi
     * @param string            $xPath
     *
     * @return array
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
            $attributes = [];
            foreach ($keyNames as $keyName) {
                $attributes[$keyName] = $this->inferDefaultFromName($keyName);
            }
            foreach ($colNames as $colName) {
                $attributes[$colName] = $this->inferDefaultFromName($colName);
            }
            if ($this->hasOwner()) {
                $attributes['ownerID'] = '$ownerID';
            }
            ksort($attributes);
            $this->tables[$tableName] = ['attributes' => $attributes];
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
        $values = [];
        /**
         * @type SimpleXMLElement $ele
         */
        foreach ($items as $ele) {
            $name = (string)$ele->getName();
            $values[$name] = $this->inferDefaultFromName($name);
        }
        if ($this->hasOwner()) {
            $values['ownerID'] = '$ownerID';
        }
        ksort($values);
        $this->tables[$tableName] = ['values' => $values];
    }
    /**
     * @type string $sectionName
     */
    protected $sectionName;
    /**
     * @type array $tables
     */
    protected $tables;
}
