<?php
declare(strict_types = 1);
/**
 * Contains SchemaUpdater class.
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

use Symfony\Component\Console\Output\OutputInterface;
use Yapeal\Container\ContainerInterface;
use Yapeal\Event\YEMAwareTrait;
use Yapeal\Exception\YapealDatabaseException;
use Yapeal\Log\Logger;

/**
 * Class SchemaUpdater
 */
class SchemaUpdater extends AbstractSchemaCommon
{
    use YEMAwareTrait;
    /**
     * @param string             $name
     * @param ContainerInterface $dic
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function __construct(string $name, ContainerInterface $dic)
    {
        $this->setDescription('Retrieves SQL from files and updates schema');
        $this->setName($name);
        $this->setDic($dic);
        parent::__construct($name);
    }
    /**
     * Configures the current command.
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $help = <<<'HELP'
The <info>%command.full_name%</info> command is used to initialize (create) a new
 schema and tables to be used by Yapeal-ng. If you already have a
 config/yapeal.yaml file setup you can use the following:

    <info>php %command.full_name%</info>

EXAMPLES:
To use a configuration file in a different location:
    <info>%command.name% -c /my/very/special/config.yaml</info>

<info>NOTE:</info>
Only the Sql section of the configuration file will be used.

You can also use the command before setting up a configuration file like so:
    <info>%command.name% -o "localhost" -d "yapeal" -u "YapealUser" -p "secret"

HELP;
        $this->addOptions($help);
        $this->setAliases(['Database:Update']);
    }
    /**
     * @param OutputInterface $output
     *
     * @throws YapealDatabaseException
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Symfony\Component\Console\Exception\LogicException
     * @throws \UnexpectedValueException
     */
    protected function processSql(OutputInterface $output)
    {
        $yem = $this->getYem();
        $latestVersion = $this->getLatestDatabaseVersion($output);
        $updateFileList = $this->getUpdateFileList($latestVersion, $output);
        if (0 === count($updateFileList)) {
            if ($output::VERBOSITY_QUIET !== $output->getVerbosity()) {
                $mess = sprintf('<info>No SQL updates newer then current version %1$s were found</info>',
                    $latestVersion);
                $yem->triggerLogEvent('Yapeal.Log.log', Logger::INFO, strip_tags($mess));
                $output->writeln($mess);
            }
            return;
        }
        $this->addDatabaseProcedure($output);
        foreach ($updateFileList as $keyName => $sqlStatements) {
            $updateVersion = basename($keyName) . '.000';
            if (false === $sqlStatements) {
                $mess = sprintf('<error>Could NOT get contents of SQL file %1$s</error>', $keyName);
                $yem->triggerLogEvent('Yapeal.Log.log', Logger::INFO, strip_tags($mess));
                $output->writeln($mess);
                throw new YapealDatabaseException($mess, 2);
            }
            $this->executeSqlStatements($sqlStatements, $keyName, $output);
            $this->updateDatabaseVersion($updateVersion);
        }
        $this->dropDatabaseProcedure($output);
    }
    /**
     * @param OutputInterface $output
     *
     * @throws YapealDatabaseException
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Symfony\Component\Console\Exception\LogicException
     * @throws \UnexpectedValueException
     */
    private function addDatabaseProcedure(OutputInterface $output)
    {
        $name = 'SchemaUpdater::addDatabaseProcedure';
        $csq = $this->getCsq();
        $this->executeSqlStatements($csq->getDropAddOrModifyColumnProcedure()
            . PHP_EOL
            . $csq->getCreateAddOrModifyColumnProcedure(),
            $name,
            $output);
        $output->writeln('');
    }
    /**
     * @param OutputInterface $output
     *
     * @throws YapealDatabaseException
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Symfony\Component\Console\Exception\LogicException
     * @throws \UnexpectedValueException
     */
    private function dropDatabaseProcedure(OutputInterface $output)
    {
        $name = 'SchemaUpdater::dropDatabaseProcedure';
        $this->executeSqlStatements($this->getCsq()
            ->getDropAddOrModifyColumnProcedure(),
            $name,
            $output);
    }
    /**
     * @param OutputInterface $output
     *
     * @return string
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    private function getLatestDatabaseVersion(OutputInterface $output): string
    {
        $sql = $this->getCsq()
            ->getLatestYapealSchemaVersion();
        try {
            $result = $this->getPdo()
                ->query($sql, \PDO::FETCH_NUM);
            $version = sprintf('%018.3F', $result->fetchColumn());
            $result->closeCursor();
        } catch (\PDOException $exc) {
            $version = '19700101000001.000';
            if ($output::VERBOSITY_QUIET !== $output->getVerbosity()) {
                $mess = sprintf('<error>Could NOT get latest database version using default %1$s</error>', $version);
                $this->getYem()
                    ->triggerLogEvent('Yapeal.Log.log', Logger::INFO, strip_tags($mess));
                $output->writeln([$sql, $mess]);
                $mess = sprintf('<info>Error message from database connection was %s</info>',
                    $exc->getMessage());
                $this->getYem()
                    ->triggerLogEvent('Yapeal.Log.log', Logger::INFO, strip_tags($mess));
                $output->writeln($mess);
            }
        }
        return $version;
    }
    /**
     * @param string          $latestVersion
     * @param OutputInterface $output
     *
     * @return array|string[]
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    private function getUpdateFileList(string $latestVersion, OutputInterface $output): array
    {
        $path = $this->getDic()['Yapeal.Sql.dir'] . 'updates/';
        if (!is_readable($path) || !is_dir($path)) {
            if ($output::VERBOSITY_QUIET !== $output->getVerbosity()) {
                $mess = sprintf('<comment>Could NOT access update directory %1$s</comment>', $path);
                $this->getYem()
                    ->triggerLogEvent('Yapeal.Log.log', Logger::INFO, strip_tags($mess));
                $output->writeln($mess);
            }
            return [];
        }
        $fileList = [];
        $platformExt = sprintf('.%1$s.sql', $this->getDic()['Yapeal.Sql.platform']);
        foreach (new \DirectoryIterator($path) as $fileInfo) {
            if ($fileInfo->isFile()) {
                $baseName = $fileInfo->getBasename();
                $firstDot = strpos($baseName, '.');
                $isSql = $firstDot === strpos($baseName, '.sql');
                $isPlatform = $firstDot === strpos($baseName, $platformExt);
                $baseName = substr($baseName, 0, $firstDot);
                if (!is_numeric($baseName) || $baseName . '.000' <= $latestVersion) {
                    continue;
                }
                $keyName = $path . $baseName;
                $notSet = !array_key_exists($keyName, $fileList) || false === $fileList[$keyName];
                if ($isPlatform) {
                    $fileList[$keyName] = $this->safeFileRead($keyName . $platformExt);
                } elseif ($isSql && $notSet) {
                    $fileList[$keyName] = $this->safeFileRead($keyName . '.sql');
                }
            }
        }
        asort($fileList);
        return $fileList;
    }
    /**
     * @return \PDOStatement
     * @throws \LogicException
     * @throws \Yapeal\Exception\YapealDatabaseException
     */
    private function getInsertStatement(): \PDOStatement
    {
        if (null === $this->insertStatement) {
            $sql = $this->getCsq()
                ->getLatestYapealSchemaVersionInsert();
            $this->insertStatement = $this->getPdo()
                ->prepare($sql);
        }
        return $this->insertStatement;
    }
    /**
     * @param string $updateVersion
     *
     * @return void
     * @throws \LogicException
     * @throws \Yapeal\Exception\YapealDatabaseException
     */
    private function updateDatabaseVersion(string $updateVersion)
    {
        $pdo = $this->getPdo();
        try {
            $pdo->beginTransaction();
            $this->getInsertStatement()
                ->execute([$updateVersion]);
            $pdo->commit();
        } catch (\PDOException $exc) {
            $mess = sprintf('PDO error message was %s', $exc->getMessage()) . PHP_EOL;
            $mess .= sprintf('Schema "version" update failed for %s',
                $updateVersion);
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw new YapealDatabaseException($mess, 2);
        }
    }
    /**
     * @var \PDOStatement $insertStatement
     */
    private $insertStatement;
}
