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
 * Copyright (C) 2014-2017 Michael Cummings
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
 * @copyright 2014-2017 Michael Cummings
 * @license   LGPL-3.0+
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Cli\Schema;

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
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function __construct(string $name, ContainerInterface $dic)
    {
        $this->setDescription('Retrieves SQL from files and initializes schema');
        $this->setDic($dic);
        $this->sqlSubs = $sqlSubs = $dic['Yapeal.Sql.Callable.GetSqlMergedSubs'];
        $this->platform = $sqlSubs['{platform}'];
        $this->schemaName = $sqlSubs['{schema}'];
        $this->createDirs = [$sqlSubs['{dir}']];
        if (!empty($sqlSubs['{appDir}'])) {
            $this->createDirs[] = $sqlSubs['{appDir}'];
        }
        $this->setCsq($dic['Yapeal.Sql.Callable.CommonQueries']);
        $this->setPdo($dic['Yapeal.Sql.Callable.Connection']);
        $this->setYem($dic['Yapeal.Event.Callable.Mediator']);
        parent::__construct($name);
    }
    /**
     * Configures the current command.
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
    <info>./vendor/bin/yc %command.name% -o "localhost" -s "yapeal-ng" -u "YapealUser" -p "secret" -l "mysql"</info>

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
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Console\Exception\LogicException
     * @throws \Symfony\Component\Console\Exception\RuntimeException
     * @throws \UnexpectedValueException
     * @see    setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('dropSchema')) {
            /**
             * @var \Symfony\Component\Console\Helper\QuestionHelper $question
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
     */
    protected function processSql(OutputInterface $output)
    {
        $yem = $this->getYem();
        if ($this->dropSchema) {
            $this->dropIfSchemaExist($output);
        }
        $fileNames = $this->getCreateFileNames();
        if (0 === count($fileNames)) {
            $mess = '<error>No SQL create files were found</error>';
            $yem->triggerLogEvent('Yapeal.Log.log', Logger::ERROR, strip_tags($mess));
            if ($output::VERBOSITY_QUIET !== $output->getVerbosity()) {
                $output->writeln($mess);
            }
            return;
        }
        foreach ($fileNames as $fileName) {
            if (false === $sqlStatements = $this->safeFileRead($fileName)) {
                $mess = sprintf('<comment>Could NOT get contents of SQL file for %s</comment>', $fileName);
                $yem->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, strip_tags($mess));
                if ($output::VERBOSITY_DEBUG <= $output->getVerbosity()) {
                    $output->writeln($mess);
                }
                continue;
            }
            $this->executeSqlStatements($sqlStatements, $fileName, $output);
        }
    }
    /**
     * @param OutputInterface $output
     *
     * @throws YapealDatabaseException
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    private function dropIfSchemaExist(OutputInterface $output)
    {
        $csq = $this->getCsq();
        $yem = $this->getYem();
        $pdo = $this->getPdo();
        $sql = $csq->getSchemaNames();
        $yem->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, 'sql - ' . $sql);
        try {
            $stmt = $pdo->query($sql);
            $schemas = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\PDOException $exc) {
            $mess = '<error>Failed to get Schema list</error>';
            $output->writeln($mess);
            throw new YapealDatabaseException(strip_tags($mess), 2, $exc);
        }
        if (!in_array($this->schemaName, $schemas)) {
            return;
        }
        $sql = $csq->getDropSchema();
        $yem->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, 'sql - ' . $sql);
        try {
            $pdo->exec($sql);
        } catch (\PDOException $exc) {
            $mess = '<error>Failed to drop Schema</error>';
            $output->writeln($mess);
            throw new YapealDatabaseException(strip_tags($mess), 2, $exc);
        }
    }
    /**
     * @return array|string[]
     */
    private function getCreateFileNames(): array
    {
        $fileExt = sprintf('.%s.sql', $this->platform);
        $globPath = sprintf('{%1$s}Create/{*%2$s,*/*%2$s}',
            implode(',', $this->createDirs),
            $fileExt);
        return glob($globPath, GLOB_BRACE | GLOB_NOESCAPE);
    }
    /**
     * @var string[] $createDirs
     */
    private $createDirs;
    /**
     * @var bool $dropSchema
     */
    private $dropSchema = false;
    /**
     * @var string $platform
     */
    private $platform;
    /**
     * @var string $schemaName
     */
    private $schemaName;
}
