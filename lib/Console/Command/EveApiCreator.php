<?php
/**
 * Contains EveApiCreator class.
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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Yapeal\Configuration\ConsoleWiring;
use Yapeal\Configuration\WiringInterface;
use Yapeal\Console\CommandToolsTrait;
use Yapeal\Container\ContainerInterface;
use Yapeal\Event\EveApiEventEmitterTrait;
use Yapeal\Event\MediatorInterface;
use Yapeal\Exception\YapealDatabaseException;
use Yapeal\Exception\YapealException;
use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Class EveApiCreator
 */
class EveApiCreator extends Command implements WiringInterface
{
    use CommandToolsTrait, EveApiEventEmitterTrait;
    /**
     * @param string|null        $name
     * @param string             $cwd
     * @param ContainerInterface $dic
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function __construct($name, $cwd, ContainerInterface $dic)
    {
        $this->setDescription(
            'Retrieves Eve Api XML from servers and creates database class, XSD, and SQL files based on the XML structure received'
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
        $this->addArgument('section_name', InputArgument::REQUIRED, 'Name of Eve Api section to retrieve.')
            ->addArgument('api_name', InputArgument::REQUIRED, 'Name of Eve Api to retrieve.')
            ->addArgument('mask', InputArgument::REQUIRED, 'Bit mask for Eve Api.')
            ->addArgument(
                'post',
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'Optional list of additional POST parameter(s) to send to server.',
                []
            );
        $this->addOption(
            'overwrite',
            null,
            InputOption::VALUE_NONE,
            'Causes command to overwrite any existing per Eve API files.'
        );
        $help = <<<EOF
The <info>%command.full_name%</info> command retrieves the XML data from the Eve Api
server and creates Yapeal Eve API Database class, xsd, and sql files for most API types.

    <info>php %command.full_name% section_name api_name mask [<post>]...</info>

EXAMPLES:
Create Char/AccountBalance class, xsd, and sql files in their respective
lib/{EveApi, Xsd, Sql}/Char/ directories.
    <info>%command.name% char AccountBalance 1 "keyID=1156" "vCode=abc123"</info>

EOF;
        $this->setHelp($help);
    }
    /** @noinspection PhpMissingParentCallCommonInspection */
    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance
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
        if ($input->hasOption('overwrite')) {
            $dic['Yapeal.Create.overwrite'] = true;
        }
        $this->wire($dic);
        $apiName = $input->getArgument('api_name');
        $sectionName = $input->getArgument('section_name');
        /**
         * @type MediatorInterface $yem
         */
        $this->yem = $dic['Yapeal.Event.Mediator'];
        /**
         * Get new Data instance from factory.
         *
         * @type EveApiReadWriteInterface $data
         */
        $data = $dic['Yapeal.Xml.Data'];
        $data->setEveApiName($apiName)
            ->setEveApiSectionName($sectionName)
            ->setEveApiArguments($posts);
        $data->addEveApiArgument('mask', $input->getArgument('mask'));
        foreach (['retrieve', 'create', 'transform', 'validate', 'cache', 'preserve'] as $eventName) {
            if (false === $this->emitEvents($data, $eventName)) {
                return 2;
            }
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
        if (0 === count($posts)) {
            return [];
        }
        $arguments = [];
        foreach ($posts as $post) {
            list($key, $value) = explode('=', $post);
            $arguments[$key] = $value;
        }
        return $arguments;
    }
}
