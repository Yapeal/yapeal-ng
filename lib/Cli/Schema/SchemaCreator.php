<?php
declare(strict_types = 1);
/**
 * Contains SchemaCreator class.
 *
 * PHP version 7.0+
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
 * <http://spdx.org/licenses/LGPL-3.0.html>.
 *
 * You should be able to find a copy of this license in the COPYING-LESSER.md
 * file. A copy of the GNU GPL should also be available in the COPYING.md file.
 *
 * @copyright 2014-2016 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Cli\Schema;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Yapeal\Container\ContainerInterface;
use Yapeal\Event\YEMAwareTrait;
use Yapeal\Exception\YapealDatabaseException;
use Yapeal\Log\Logger;
use Yapeal\Sql\CSQAwareTrait;

/**
 * Class SchemaCreator
 */
class SchemaCreator extends AbstractSchemaCommon
{
    use CSQAwareTrait;
    use YEMAwareTrait;
    /**
     * @param string             $name
     * @param ContainerInterface $dic
     *
     * @throws \Symfony\Component\Console\Exception\LogicException
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    public function __construct(string $name, ContainerInterface $dic)
    {
        $this->setDescription('Retrieves SQL from files and initializes schema');
        $this->setDic($dic);
        parent::__construct($name);
    }
    /** @noinspection PhpMissingParentCallCommonInspection */
    /**
     * Configures the current command.
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $help = <<<'HELP'
The <info>%command.name%</info> command is used to create (initialize) a
new schema and tables to be used by Yapeal-ng. If you already have a
config/yapeal.yaml file setup you should be able to use the following:
    <info>php bin/yc %command.name%</info>
from the directory where it is installed. If you have required Yapeal-ng in
your application with Composer you'll find it in the vendor/bin/yc directory.

EXAMPLES:
These examples assume you are in the base directory of your application where
your composer.json file is found.

To use a configuration file in a different location:
    <info>./vendor/bin/yc %command.name% -c /my/very/special/config.yaml</info>
You can also use the command before setting up a configuration file like so:
    <info>./vendor/bin/yc %command.name% -o "localhost" -d "yapeal-ng" -u "YapealUser" -p "secret" -l "mysql"</info>

Windows users can use ./vendor/bin/yc.bat in place of ./vendor/bin/yc above.

Finally you can use the <comment>VERY DANGEROUS</comment> '--dropSchema' option to also drop an
exist schema and all it's tables and their data before re-creating everything.
Make sure you have a good backup of your schema(database) before using this
option.
HELP;
        $this->addOptions($help);
        $desc = 'Drop existing schema(database) before re-creating.'
            . ' <comment>Warning all the tables will be dropped as well!</comment>';
        $this->addOption('dropSchema', null, InputOption::VALUE_NONE, $desc);
        $this->setAliases(['Database:Init']);
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
     * @return int null or 0 if everything went fine, or an error code
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Console\Exception\LogicException
     * @throws \Symfony\Component\Console\Exception\RuntimeException
     * @throws \Yapeal\Exception\YapealDatabaseException
     * @throws \Yapeal\Exception\YapealException
     *
     * @see    setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->hasYem()) {
            $this->setYem($this->getDic()['Yapeal.Event.Mediator']);
        }
        if ($input->getOption('dropSchema')) {
            /**
             * @var QuestionHelper $question
             */
            $question = $this->getHelper('question');
            $mess = '<comment>Are you sure you want to drop the schema(database)'
                . ' and it\'s tables with their data?(no)</comment>';
            $confirm = new ConfirmationQuestion($mess, false);
            $this->dropSchema = (bool)$question->ask($input, $output, $confirm);
            if (!$this->dropSchema) {
                $output->writeln('<info>Ignoring drop schema(database)</info>');
            }
        }
        return parent::execute($input, $output);
    }
    /**
     * @param OutputInterface $output
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Symfony\Component\Console\Exception\LogicException
     * @throws \UnexpectedValueException
     * @throws \Yapeal\Exception\YapealDatabaseException
     * @throws \Yapeal\Exception\YapealFileSystemException
     */
    protected function processSql(OutputInterface $output)
    {
        $csq = $this->getCsq();
        $yem = $this->getYem();
        $this->processSchemaQueries($output)
            ->processSpecialYapealTables($output);
        foreach ($this->getCreateMethodNames() as $methodName) {
            try {
                $sqlStatements = $csq->$methodName();
            } catch (\BadMethodCallException $exc) {
                if ($output::VERBOSITY_QUIET !== $output->getVerbosity()) {
                    $mess = sprintf('<error>Could NOT get contents of SQL file for %s</error>', $methodName);
                    $output->writeln($mess);
                    $yem->triggerLogEvent('Yapeal.Log.log', Logger::INFO, strip_tags($mess));
                }
                continue;
            }
            $this->executeSqlStatements($sqlStatements, 'SchemaCreator::' . $methodName, $output);
        }
    }
    /**
     * @return array
     * @throws \LogicException
     * @internal param OutputInterface $output
     *
     */
    private function getCreateMethodNames(): array
    {
        $sql = $this->getCsq()
            ->getSortedMethodNames();
        $rows = $this->getPdo()
            ->query($sql)
            ->fetchAll(\PDO::FETCH_ASSOC);
        $methodNames = [];
        foreach ($rows as $row) {
            $methodNames[] = sprintf('create%s%s', ucfirst($row['sectionName']), $row['apiName']);
        }
        return $methodNames;
    }
    /**
     * @param OutputInterface $output
     *
     * @return self Fluent interface.
     * @throws YapealDatabaseException
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Symfony\Component\Console\Exception\LogicException
     * @throws \UnexpectedValueException
     */
    private function processSchemaQueries(OutputInterface $output): self
    {
        if ($this->dropSchema) {
            try {
            $this->executeSqlStatements($this->getCsq()
                ->getDropSchema(),
                'SchemaCreator::DropSchema',
                $output);
            } catch (\BadMethodCallException $exc) {
                $mess = '<error>Failed to get drop schema SQL</error>';
                if ($output::VERBOSITY_QUIET !== $output->getVerbosity()) {
                    $output->writeln($mess);
                    $this->getYem()
                        ->triggerLogEvent('Yapeal.Log.error', Logger::ERROR, strip_tags($mess));
                }
                throw new YapealDatabaseException(strip_tags($mess), 2, $exc);
            }
        }
        try {
        $this->executeSqlStatements($this->getCsq()
            ->getCreateSchema(),
            'SchemaCreator::CreateSchema',
            $output);
        } catch (\BadMethodCallException $exc) {
            $mess = '<error>Failed to get create schema SQL</error>';
            if ($output::VERBOSITY_QUIET !== $output->getVerbosity()) {
                $output->writeln($mess);
                $this->getYem()
                    ->triggerLogEvent('Yapeal.Log.error', Logger::ERROR, strip_tags($mess));
            }
            throw new YapealDatabaseException(strip_tags($mess), 2, $exc);
        }
        return $this;
    }
    /**
     * @param OutputInterface $output
     *
     * @return self Fluent interface.
     * @throws YapealDatabaseException
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Symfony\Component\Console\Exception\LogicException
     * @throws \UnexpectedValueException
     */
    private function processSpecialYapealTables(OutputInterface $output): self
    {
        try {
            $this->executeSqlStatements($this->getCsq()
                ->getCreateYapealSchemaVersion(),
                'SchemaCreator::CreateYapealSchemaVersion',
                $output);
        } catch (\BadMethodCallException $exc) {
            $mess = '<error>Failed to get create table SQL for critical Yapeal schema version table</error>';
            if ($output::VERBOSITY_QUIET !== $output->getVerbosity()) {
                $output->writeln($mess);
                $this->getYem()->triggerLogEvent('Yapeal.Log.error', Logger::ERROR, strip_tags($mess));
            }
            throw new YapealDatabaseException(strip_tags($mess), 2, $exc);
        }
        try {
        $this->executeSqlStatements($this->getCsq()
                ->getCreateYapealEveApi(),
                'SchemaCreator::CreateYapealEveApi',
                $output);
        } catch (\BadMethodCallException $exc) {
            $mess = '<error>Failed to get create table SQL for critical Yapeal Eve Api table</error>';
            if ($output::VERBOSITY_QUIET !== $output->getVerbosity()) {
                $output->writeln($mess);
                $this->getYem()
                    ->triggerLogEvent('Yapeal.Log.error', Logger::ERROR, strip_tags($mess));
            }
            throw new YapealDatabaseException(strip_tags($mess), 2, $exc);
        }
        return $this;
    }
    /**
     * @var bool $dropSchema
     */
    private $dropSchema = false;
}
