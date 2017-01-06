<?php
declare(strict_types = 1);
/**
 * Contains EveApiCreator class.
 *
 * PHP version 7.0+
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2015-2017 Michael Cummings
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
 * @copyright 2015-2017 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Cli\Developer\EveApi;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Yapeal\Cli\ConfigFileTrait;
use Yapeal\CommonToolsTrait;
use Yapeal\Container\ContainerInterface;
use Yapeal\Event\EveApiEventEmitterTrait;
use Yapeal\Event\YEMAwareInterface;
use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Class EveApiCreator
 */
class EveApiCreator extends Command implements YEMAwareInterface
{
    use CommonToolsTrait, ConfigFileTrait, EveApiEventEmitterTrait;
    /**
     * @param string             $name
     * @param ContainerInterface $dic
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function __construct(string $name, ContainerInterface $dic)
    {
        $desc = 'Retrieves Eve Api XML from CCP servers and creates database class, XSD, and SQL files based on the XML'
            . ' structure received';
        $this->setDescription($desc);
        $this->setName($name);
        $this->setDic($dic);
        $this->setYem($dic['Yapeal.Event.Callable.Mediator']);
        parent::__construct($name);
    }
    /**
     * @param string $apiName
     * @param string $sectionName
     * @param array  $posts
     *
     * @return int
     * @throws \LogicException
     *
     */
    public function createEveApi(string $apiName, string $sectionName, array $posts): int
    {
        /**
         * Get new Data instance from factory.
         *
         * @var EveApiReadWriteInterface $data
         */
        $data = $this->getDic()['Yapeal.Xml.Callable.Data'];
        $data->setEveApiName($apiName)
            ->setEveApiSectionName($sectionName)
            ->setEveApiArguments($posts);
        /*
         * Create can't use pre-processed Eve Api XML, it needs to be unaltered version directly from the servers.
         * For now use Raw preserve since DB tables are not added automatically which would cause normal preservers to
         * fail and add junk to the logs.
         * NOTE: Need to decide if implementing a hybrid SQL update/init system is worth doing.
         */
        $events = [
            'retrieve' => 'Yapeal.EveApi.Raw',
            'create' => 'Yapeal.EveApi',
            'transform' => 'Yapeal.EveApi',
            'validate' => 'Yapeal.EveApi',
            'preserve' => 'Yapeal.EveApi.Raw'
        ];
        foreach ($events as $eventName => $eventPrefix) {
            if (false === $this->emitEvents($data, $eventName, $eventPrefix)) {
                return 2;
            }
        }
        return 0;
    }
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $help = <<<'EOF'
The <info>%command.name%</info> command is  used by Yapeal--ng developers to retrieve
the XML data from the Eve Api server and creates Yapeal Eve API Database class,
xsd, and sql files for most API types. Application developers will not
generally find it useful.

    <info>php bin/yc %command.name% section_name api_name mask [<post>]...</info>

EXAMPLES:
Create Char/AccountBalance class, xsd, and sql files in their respective
lib/{EveApi, Xsd, Sql}/Char/ directories.
    <info>bin/yc %command.name% char AccountBalance 1 "keyID=1156" "vCode=abc123"</info>

EOF;
        $this->addConfigFileOption();
        $this->addArgument('section_name', InputArgument::REQUIRED, 'Name of Eve Api section to retrieve.')
            ->addArgument('api_name', InputArgument::REQUIRED, 'Name of Eve Api to retrieve.')
            ->addArgument('mask', InputArgument::REQUIRED, 'Bit mask for Eve Api.')
            ->addArgument('post',
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'Optional list of additional POST parameter(s) to send to server.',
                [])
            ->addOption('overwrite',
                null,
                InputOption::VALUE_NONE,
                'Causes command to overwrite any existing per Eve API files.')
            ->setHelp($help);
    }
    /** @noinspection PhpMissingParentCallCommonInspection */
    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return int null or 0 if everything went fine, or an error code
     * @throws \DomainException
     * @throws \LogicException
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws \Yapeal\Exception\YapealException
     *
     * @see    setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $posts = $this->processPost($input);
        $posts['mask'] = $input->getArgument('mask');
        $dic = $this->getDic();
        $options = $input->getOptions();
        $dic['Yapeal.Create.overwrite'] = $options['overwrite'];
        if (array_key_exists('configFile', $options)) {
            $this->processConfigFile($options['configFile'], $dic);
        }
        return $this->createEveApi($input->getArgument('api_name'), $input->getArgument('section_name'), $posts);
    }
    /**
     * @param InputInterface $input
     *
     * @return array
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function processPost(InputInterface $input): array
    {
        $posts = (array)$input->getArgument('post');
        if (0 === count($posts)) {
            return [];
        }
        $arguments = [];
        foreach ($posts as $post) {
            if (false === strpos($post, '=')) {
                continue;
            }
            list($key, $value) = explode('=', $post);
            $arguments[$key] = $value;
        }
        return $arguments;
    }
}
