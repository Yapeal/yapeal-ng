<?php
/**
 * Contains DatabaseUpdater class.
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

use DirectoryIterator;
use InvalidArgumentException;
use LogicException;
use PDO;
use PDOException;
use Symfony\Component\Console\Output\OutputInterface;
use Yapeal\Container\ContainerInterface;
use Yapeal\Exception\YapealConsoleException;
use Yapeal\Exception\YapealDatabaseException;

/**
 * Class DatabaseUpdater
 */
class DatabaseUpdater extends AbstractDatabaseCommon
{
    /**
     * @param string|null        $name
     * @param string             $cwd
     * @param ContainerInterface $dic
     *
     * @throws LogicException
     * @throws InvalidArgumentException
     */
    public function __construct($name, $cwd, ContainerInterface $dic)
    {
        $this->setDescription(
            'Retrieves SQL from files and updates database'
        );
        $this->setName($name);
        $this->setCwd($cwd);
        $this->setDic($dic);
        parent::__construct($name);
    }
    /**
     * @param OutputInterface $output
     *
     * @throws \InvalidArgumentException
     * @throws YapealDatabaseException
     * @throws YapealConsoleException
     */
    protected function addDatabaseProcedure(OutputInterface $output)
    {
        $name = 'DatabaseUpdater::addDatabaseProcedure';
        $output->writeln($name);
        $csq = $this->getCsq();
        $this->executeSqlStatements(
            $csq->getDropAddOrModifyColumnProcedure()
            . PHP_EOL
            . $csq->getCreateAddOrModifyColumnProcedure(),
            $name,
            $output
        );
        $output->writeln('');
    }
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->addOptions();
        $help
            = <<<'HELP'
The <info>%command.full_name%</info> command is used to initialize (create) a new
 database and tables to be used by Yapeal. If you already have a
 config/yapeal.yaml file setup you can use the following:

    <info>php %command.full_name%</info>

EXAMPLES:
To use a configuration file in a different location:
    <info>%command.name% -c /my/very/special/config.yaml</info>

<info>NOTE:</info>
Only the Database section of the configuration file will be used.

You can also use the command before setting up a configuration file like so:
    <info>%command.name% -o "localhost" -d "yapeal" -u "YapealUser" -p "secret"

HELP;
        $this->setHelp($help);
    }
    /**
     * @param OutputInterface $output
     *
     * @throws \InvalidArgumentException
     * @throws YapealConsoleException
     * @throws YapealDatabaseException
     */
    protected function dropDatabaseProcedure(OutputInterface $output)
    {
        $name = 'DatabaseUpdater::dropDatabaseProcedure';
        $output->writeln($name);
        $this->executeSqlStatements(
            $this->getCsq()
                ->getDropAddOrModifyColumnProcedure(),
            $name,
            $output
        );
        $output->writeln('');
    }
    /**
     * @param OutputInterface $output
     *
     * @return string
     * @throws \InvalidArgumentException
     * @throws YapealConsoleException
     * @throws YapealDatabaseException
     */
    protected function getLatestDatabaseVersion(OutputInterface $output)
    {
        $sql = $this->getCsq()
            ->getUtilLatestDatabaseVersion();
        try {
            $result = $this->getPdo()
                ->query($sql, PDO::FETCH_NUM);
            $version = $result->fetchColumn();
            $result->closeCursor();
        } catch (PDOException $exc) {
            $mess
                = '<warning>Could NOT get latest database version using default 197001010001</warning>';
            $output->writeln([$sql, $mess]);
            $mess = sprintf(
                '<info>Error message from database connection was %s</info>',
                $exc->getMessage()
            );
            $output->writeln($mess);
            $version = '197001010001';
        }
        return sprintf('%1$012s', $version);
    }
    /**
     * @param OutputInterface $output
     *
     * @return string[]
     * @throws InvalidArgumentException
     * @throws YapealConsoleException
     */
    protected function getUpdateFileList(OutputInterface $output)
    {
        $fileNames = [];
        $path = $this->getDic()['Yapeal.Sql.dir'] . 'updates/';
        if (!is_readable($path) || !is_dir($path)) {
            $mess = sprintf(
                '<info>Could NOT access update directory %1$s</info>',
                $path
            );
            $output->writeln($mess);
            return $fileNames;
        }
        foreach (new DirectoryIterator($path) as $fileInfo) {
            if ($fileInfo->isDot() || $fileInfo->isDir()) {
                continue;
            };
            if ('sql' !== $fileInfo->getExtension()) {
                continue;
            }
            $fileNames[] = $this->getFpn()
                ->normalizeFile($fileInfo->getPathname());
        }
        asort($fileNames);
        return $fileNames;
    }
    /**
     * @param OutputInterface $output
     *
     * @throws InvalidArgumentException
     * @throws YapealConsoleException
     * @throws YapealDatabaseException
     */
    protected function processSql(OutputInterface $output)
    {
        $this->addDatabaseProcedure($output);
        foreach ($this->getUpdateFileList($output) as $fileName) {
            /** @noinspection DisconnectedForeachInstructionInspection */
            $latestVersion = $this->getLatestDatabaseVersion($output);
            if (!is_file($fileName)) {
                $mess = sprintf(
                    '<info>Could NOT find SQL file %1$s</info>',
                    $fileName
                );
                $output->writeln($mess);
                continue;
            }
            $updateVersion = basename($fileName, '.sql');
            if ($updateVersion <= $latestVersion) {
                $mess = sprintf(
                    '<info>Skipping SQL file %1$s since its <= the latest database version %2$s</info>',
                    basename($fileName),
                    $latestVersion
                );
                $output->writeln($mess);
                continue;
            }
            $sqlStatements = file_get_contents($fileName);
            if (false === $sqlStatements) {
                $mess = sprintf(
                    '<warning>Could NOT get contents of SQL file %1$s</warning>',
                    $fileName
                );
                $output->writeln($mess);
                continue;
            }
            $output->writeln($fileName);
            $this->executeSqlStatements($sqlStatements, $fileName, $output);
            $this->updateDatabaseVersion($updateVersion);
            /** @noinspection DisconnectedForeachInstructionInspection */
            $output->writeln('');
        }
        $this->dropDatabaseProcedure($output);
    }
    /**
     * @param string $updateVersion
     *
     * @return DatabaseUpdater
     * @throws YapealConsoleException
     * @throws YapealDatabaseException
     */
    protected function updateDatabaseVersion($updateVersion)
    {
        $sql = $this->getCsq()
            ->getUtilLatestDatabaseVersionUpdate();
        try {
            $pdo = $this->getPdo();
            $pdo->beginTransaction();
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$updateVersion]);
            $pdo->commit();
        } catch (PDOException $exc) {
            $mess = $sql . PHP_EOL;
            $mess .=
                sprintf('Database error message was %s', $exc->getMessage())
                . PHP_EOL;
            $mess .= sprintf(
                'Database "version" update failed for %1$s',
                $updateVersion
            );
            if ($this->getPdo()->inTransaction()) {
                $this->getPdo()
                    ->rollBack();
            }
            throw new YapealDatabaseException($mess, 2);
        }
        return $this;
    }
}