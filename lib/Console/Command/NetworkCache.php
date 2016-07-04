<?php
/**
 * Contains NetworkCache class.
 *
 * PHP version 5.5
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2014-2016 Michael Cummings
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
 * @copyright 2014-2016 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yapeal\CommonToolsTrait;
use Yapeal\Container\ContainerInterface;
use Yapeal\Event\EveApiEventEmitterTrait;
use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Class NetworkCache
 */
class NetworkCache extends Command
{
    use CommonToolsTrait, ConfigFileTrait, EveApiEventEmitterTrait;
    /**
     * @param string|null        $name
     * @param ContainerInterface $dic
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function __construct($name, ContainerInterface $dic)
    {
        $this->setDescription('Retrieves Eve Api XML from servers and puts it in file');
        $this->setName($name);
        $this->setDic($dic);
        parent::__construct($name);
    }
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $help = <<<'EOF'
The <info>%command.full_name%</info> command retrieves the XML data from the
Eve Api server and stores it in a file. It will put the file in the normal
cache directory per the configuration settings.

    <info>php %command.full_name% section_name api_name</info>

EXAMPLES:
Save current server status to the cache directory.
    <info>%command.name% server ServerStatus</info>

EOF;
        $this->addConfigFileOption();
        $this->addArgument('section_name', InputArgument::REQUIRED, 'Name of Eve Api section to retrieve.')
            ->addArgument('api_name', InputArgument::REQUIRED, 'Name of Eve Api to retrieve.')
            ->addArgument('post', InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'Optional list of additional POST parameter(s) to send to server.', [])
            ->setHelp($help);
    }
    /** @noinspection PhpMissingParentCallCommonInspection */
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
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Yapeal\Exception\YapealException
     *
     * @see    setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var \Symfony\Component\Console\Output\Output $output
         */
        $posts = $this->processPost($input);
        $dic = $this->getDic();
        $options = $input->getOptions();
        if (!empty($options['configFile'])) {
            $this->processConfigFile($options['configFile'], $dic);
        }
        $apiName = $input->getArgument('api_name');
        $sectionName = $input->getArgument('section_name');
        $this->setYem($dic['Yapeal.Event.Mediator']);
        /**
         * Get new Data instance from factory.
         *
         * @var EveApiReadWriteInterface $data
         */
        $data = $dic['Yapeal.Xml.Data'];
        $data->setEveApiName($apiName)
            ->setEveApiSectionName($sectionName)
            ->setEveApiArguments($posts);
        if ($output::VERBOSITY_QUIET !== $output->getVerbosity()) {
            $mess = sprintf('<info>Starting %1$s of%2$s</info>', $this->getName(),
                $this->createEveApiMessage('', $data));
            $output->writeln($mess);
        }
        foreach (['retrieve', 'cache'] as $eventName) {
            $this->emitEvents($data, $eventName);
        }
        if (false === $data->getEveApiXml()) {
            if ($output::VERBOSITY_QUIET !== $output->getVerbosity()) {
                $mess = sprintf(
                    '<error>Could NOT retrieve Eve Api data for %1$s/%2$s</error>',
                    strtolower($sectionName),
                    $apiName
                );
                $output->writeln($mess);
            }
            return 2;
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
