<?php
/**
 * Contains EveApiRetriever class.
 *
 * PHP version 5.5
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2014-2015 Michael Cummings
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
 * @copyright 2014-2015 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Console\Command;

use FilePathNormalizer\FilePathNormalizerTrait;
use InvalidArgumentException;
use LogicException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Yapeal\Configuration\ConsoleWiring;
use Yapeal\Configuration\WiringInterface;
use Yapeal\Console\CommandToolsTrait;
use Yapeal\Container\ContainerInterface;
use Yapeal\Exception\YapealConsoleException;
use Yapeal\Exception\YapealDatabaseException;
use Yapeal\Exception\YapealException;
use Yapeal\Xml\EveApiPreserverInterface;
use Yapeal\Xml\EveApiReadWriteInterface;
use Yapeal\Xml\EveApiRetrieverInterface;

/**
 * Class EveApiRetriever
 */
class EveApiRetriever extends Command implements WiringInterface
{
    use CommandToolsTrait, FilePathNormalizerTrait;
    /**
     * @param string|null        $name
     * @param string             $cwd
     * @param ContainerInterface $dic
     *
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    public function __construct($name, $cwd, ContainerInterface $dic)
    {
        $this->setDescription(
            'Retrieves Eve Api XML from servers and puts it in file'
        );
        $this->setName($name);
        $this->setCwd($cwd);
        $this->setDic($dic);
        parent::__construct($name);
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
            'post',
            InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
            'Optional list of additional POST parameter(s) to send to server.',
            []
        );
        $this->addOption(
            'directory',
            'd',
            InputOption::VALUE_REQUIRED,
            'Directory that XML will be sent to.'
        );
        $help
            = <<<EOF
The <info>%command.full_name%</info> command retrieves the XML data from the Eve Api
server and stores it in a file. By default it will put the file in the current
working directory.

    <info>php %command.full_name% section_name api_name</info>

EXAMPLES:
Save current server status in current directory.
    <info>%command.name% server ServerStatus</info>

EOF;
        $this->setHelp($help);
    }
    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return int|null null or 0 if everything went fine, or an error code
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws YapealException
     * @throws YapealConsoleException
     * @see    setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
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
        }
        $this->wire($this->getDic());
        /**
         * @type EveApiReadWriteInterface $data
         */
        $data = $this->getDic()['Yapeal.Xml.Data'];
        $data = $data->setEveApiName($input->getArgument('api_name'))
            ->setEveApiSectionName($input->getArgument('section_name'))
            ->setEveApiArguments($posts);
        $retriever = $this->getDic()['Yapeal.Network.Retriever'];
        /**
         * @type EveApiRetrieverInterface $retriever
         */
        $retriever->retrieveEveApi($data);
        if (false !== $data->getEveApiXml()) {
            /**
             * @type EveApiPreserverInterface $preserver
             */
            $preserver = $this->getDic()['Yapeal.Xml.Preserver'];
            $preserver->preserveEveApi($data);
        }
    }
}