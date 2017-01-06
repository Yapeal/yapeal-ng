<?php
declare(strict_types = 1);
/**
 * Contains AbstractSchemaCommon class.
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
namespace Yapeal\Console\Schema;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Yapeal\Console\ConfigFileTrait;
use Yapeal\Console\VerbosityMappingTrait;
use Yapeal\CommonToolsTrait;
use Yapeal\Container\DicAwareInterface;
use Yapeal\Event\YEMAwareInterface;
use Yapeal\Event\YEMAwareTrait;
use Yapeal\Exception\YapealDatabaseException;
use Yapeal\FileSystem\SafeFileHandlingTrait;
use Yapeal\Log\Logger;
use Yapeal\Sql\SqlCleanupTrait;

/**
 * Class AbstractSchemaCommon
 */
abstract class AbstractSchemaCommon extends Command implements YEMAwareInterface, DicAwareInterface
{
    use CommonToolsTrait;
    use ConfigFileTrait;
    use SafeFileHandlingTrait;
    use SqlCleanupTrait;
    use VerbosityMappingTrait;
    use YEMAwareTrait;
    /**
     * Sets the help message and all the common options used by the Database:* commands.
     *
     * @param string $help Command help text.
     *
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    protected function addOptions(string $help)
    {
        $this->addConfigFileOption();
        $this->addOption('schema', 's', InputOption::VALUE_REQUIRED, 'Name of the schema(database).')
            ->addOption('hostName', 'o', InputOption::VALUE_REQUIRED, 'Host name for database server.')
            ->addOption('password', 'p', InputOption::VALUE_REQUIRED, 'Password used to access schema.')
            ->addOption('platform',
                'l',
                InputOption::VALUE_REQUIRED,
                'Platform of PDO driver. Currently only "mysql" can be used.',
                'mysql')
            ->addOption('port',
                null,
                InputOption::VALUE_REQUIRED,
                'Port number for remote server. Only needed if using http connection.')
            ->addOption('tablePrefix', 't', InputOption::VALUE_REQUIRED, 'Prefix for schema table names.')
            ->addOption('userName', 'u', InputOption::VALUE_REQUIRED, 'User name used to access schema.')
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
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \UnexpectedValueException
     * @see    setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->applyVerbosityMap($output);
        $this->processCliOptions($input);
        $this->processSql($output);
        return 0;
    }
    /**
     * @param string          $sqlStatements
     * @param string          $fileName
     * @param OutputInterface $output
     *
     * @throws YapealDatabaseException
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Symfony\Component\Console\Exception\LogicException
     * @throws \UnexpectedValueException
     */
    protected function executeSqlStatements(string $sqlStatements, string $fileName, OutputInterface $output)
    {
        $pdo = $this->getPdo();
        $yem = $this->getYem();
        $statements = explode(';', $this->getCleanedUpSql($sqlStatements, $this->getSqlSubs()));
        $statements = array_filter($statements,
            function ($statement) {
                return '' !== trim($statement);
            });
        $progress = null;
        if ($output::VERBOSITY_QUIET !== $output->getVerbosity()) {
            if (false === strpos($fileName, '::')) {
                $mess = sprintf('<info>Execute %1$s/%2$s</info>',
                    basename(dirname($fileName)),
                    basename($fileName));
            } else {
                $mess = sprintf('<info>Execute %s</info>', $fileName);
            }
            $yem->triggerLogEvent('Yapeal.Log.log', Logger::INFO, strip_tags($mess));
            $output->writeln($mess);
            $progress = $this->createProgressBar($output, count($statements));
        }
        foreach ($statements as $statement => $sql) {
            try {
                // Last minute replacement for procedures that has to be done
                // here so as not to break statements.
                $sql = str_replace('{semiColon}', ';', trim($sql));
                null !== $progress && $progress->clear();
                $yem->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, 'sql - ' . $sql);
                $pdo->exec($sql);
                if (null !== $progress) {
                    $progress->display();
                    $progress->setMessage('<comment>executing</comment>');
                    $progress->advance();
                }
            } catch (\PDOException $exc) {
                if (null !== $progress) {
                    $progress->setMessage('<error>Failed</error>');
                    $progress->advance();
                    $output->writeln('');
                }
                $mess = '<error>SQL error in statement ' . $statement . '</error>';
                $output->writeln($mess);
                throw new YapealDatabaseException(strip_tags($mess), 2, $exc);
            }
        }
        if (null !== $progress) {
            $progress->setMessage('<info>Finished</info>');
            $progress->finish();
            $output->writeln('');
        }
    }
    /**
     * @return array
     * @throws \LogicException
     */
    protected function getSqlSubs()
    {
        return $this->sqlSubs;
    }
    /**
     * @param InputInterface $input
     *
     * @return static Fluent interface.
     * @throws \DomainException
     * @throws \LogicException
     */
    protected function processCliOptions(InputInterface $input)
    {
        $dic = $this->getDic();
        $options = $input->getOptions();
        if (!empty($options['configFile'])) {
            $this->processConfigFile($options['configFile'], $dic);
        }
        // TODO: Needs to be fixed for per platform config settings.
        $base = 'Yapeal.Sql.';
        foreach (['schema', 'hostName', 'password', 'platform', 'tablePrefix', 'userName'] as $option) {
            if (array_key_exists($option, $options) && null !== $options[$option]) {
                $dic[$base . $option] = $options[$option];
            }
        }
        return $this;
    }
    /**
     * @param OutputInterface $output
     */
    abstract protected function processSql(OutputInterface $output);
    /**
     * @var array $sqlSubs Holds a list of Sql section replacement pairs.
     */
    protected $sqlSubs;
    /**
     * @param OutputInterface $output
     * @param int             $statementCount
     *
     * @return ProgressBar
     */
    private function createProgressBar(OutputInterface $output, int $statementCount): ProgressBar
    {
        $progress = new ProgressBar($output);
        $progress->setRedrawFrequency(1);
        $progress->setBarWidth(47);
        $progress->setFormat('%current:2s%/%max:2s% [%bar%] %percent:3s%% %elapsed:6s% %message%');
        $progress->setMessage('<info>starting</info>');
        $progress->start($statementCount);
        return $progress;
    }
}
