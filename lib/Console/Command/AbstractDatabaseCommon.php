<?php
declare(strict_types=1);
/**
 * Contains AbstractDatabaseCommon class.
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
namespace Yapeal\Console\Command;

use FilePathNormalizer\FilePathNormalizerTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Yapeal\CommonToolsTrait;
use Yapeal\Exception\YapealDatabaseException;

/**
 * Class AbstractDatabaseCommon
 */
abstract class AbstractDatabaseCommon extends Command
{
    use CommonToolsTrait, ConfigFileTrait, FilePathNormalizerTrait;
    /**
     * Sets the help message and all the common options used by the Database:* commands.
     *
     * @param string $help Command help text.
     */
    protected function addOptions($help)
    {
        $this->addConfigFileOption();
        $this->addOption('database', 'd', InputOption::VALUE_REQUIRED, 'Name of the database.')
            ->addOption('hostName', 'o', InputOption::VALUE_REQUIRED, 'Host name for database server.')
            ->addOption('password', 'p', InputOption::VALUE_REQUIRED, 'Password used to access database.')
            ->addOption('platform', null, InputOption::VALUE_REQUIRED,
                'Platform of database driver. Currently only "mysql" can be used.')
            ->addOption('port', null, InputOption::VALUE_REQUIRED,
                'Port number for remote server. Only needed if using http connection.')
            ->addOption('tablePrefix', 't', InputOption::VALUE_REQUIRED, 'Prefix for database table names.')
            ->addOption('userName', 'u', InputOption::VALUE_REQUIRED, 'User name used to access database.')
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
     * @throws \LogicException
     * @throws \Yapeal\Exception\YapealException
     *
     * @see    setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->processCliOptions($input);
        return $this->processSql($output);
    }
    /**
     * @param string          $sqlStatements
     * @param string          $fileName
     * @param OutputInterface $output
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Symfony\Component\Console\Exception\LogicException
     * @throws \Yapeal\Exception\YapealDatabaseException
     */
    protected function executeSqlStatements($sqlStatements, $fileName, OutputInterface $output)
    {
        $replacements = [
            ';' => '',
            '{database}' => $this->getDic()['Yapeal.Sql.database'],
            '{engine}' => $this->getDic()['Yapeal.Sql.engine'],
            '{ engine}' => $this->getDic()['Yapeal.Sql.engine'],
            '{table_prefix}' => $this->getDic()['Yapeal.Sql.tablePrefix'],
            '$$' => ';'
        ];
        $pdo = $this->getPdo();
        // Split up SQL into statements on ';'.
        // Replace {database}, {table_prefix}, {engine}, ';', and '$$' in statements.
        /**
         * @var string[] $statements
         */
        $statements = str_replace(array_keys($replacements), array_values($replacements), explode(';', $sqlStatements));
        // 5 is a 'magic' number that I think is shorter than any legal SQL statement.
        $statements = array_filter($statements, function ($value) {
            return 5 <= strlen(trim($value));
        });
        $progress = null;
        if ($output::VERBOSITY_QUIET !== $output->getVerbosity()) {
            if (false === strpos($fileName, '::')) {
                $mess = sprintf('<info>Execute %1$s/%2$s</info>', basename(dirname($fileName)),
                    basename($fileName, '.sql'));
            } else {
                $mess = sprintf('<info>Execute %s</info>', $fileName);
            }
            $output->writeln($mess);
            $progress = $this->createProgressBar($output, count($statements));
        }
        foreach ($statements as $statement => $sql) {
            $sql = trim($sql);
            try {
                $pdo->exec($sql);
                if (null !== $progress) {
                    $progress->setMessage('<comment>executing</comment>');
                    $progress->advance();
                }
            } catch (\PDOException $exc) {
                if (null !== $progress) {
                    $progress->setMessage('<error>Failed</error>');
                    $progress->finish();
                    $output->writeln('');
                }
                $mess = $sql . PHP_EOL;
                $mess .= sprintf(
                    'Sql failed in %1$s on statement %2$s with (%3$s) %4$s',
                    $fileName,
                    $statement,
                    $exc->getCode(),
                    $exc->getMessage()
                );
                throw new YapealDatabaseException($mess, 2);
            }
        }
        if (null !== $progress) {
            $progress->setMessage('');
            $progress->finish();
            $output->writeln('');
        }
    }
    /**
     * @param InputInterface $input
     *
     * @return AbstractDatabaseCommon
     * @throws \DomainException
     * @throws \LogicException
     * @throws \Yapeal\Exception\YapealException
     */
    protected function processCliOptions(InputInterface $input)
    {
        $dic = $this->getDic();
        $options = $input->getOptions();
        if (!empty($options['configFile'])) {
            $this->processConfigFile($options['configFile'], $dic);
        }
        $base = 'Yapeal.Sql.';
        foreach (['class', 'database', 'hostName', 'password', 'platform', 'tablePrefix', 'userName'] as $option) {
            if (!empty($options[$option])) {
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
     * @param OutputInterface $output
     * @param int             $statementCount
     *
     * @return ProgressBar
     */
    private function createProgressBar(OutputInterface $output, $statementCount)
    {
        $progress = new ProgressBar($output);
        $progress->setRedrawFrequency(1);
        $progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s% %message%');
        $progress->setMessage('<info>starting</info>');
        $progress->start($statementCount);
        $progress->setBarWidth(min(4 * $statementCount + 2, 50));
        return $progress;
    }
}
