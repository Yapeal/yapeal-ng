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
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function __construct(string $name, ContainerInterface $dic)
    {
        $this->setDescription('Retrieves SQL from files and updates schema');
        $this->setDic($dic);
        $this->platform = $dic['Yapeal.Sql.platform'];
        $this->updateDirs = [$dic['Yapeal.Sql.dir']];
        if (!empty($dic['Yapeal.Sql.appDir'])) {
            $this->updateDirs[] = $dic['Yapeal.Sql.appDir'];
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
        $fileNames = $this->getUpdateFileNames($latestVersion);
        if (0 === count($fileNames)) {
            $mess = sprintf('<info>No SQL update files newer then current schema version %s were found</info>', $latestVersion);
            $yem->triggerLogEvent('Yapeal.Log.log', Logger::INFO, strip_tags($mess));
            if ($output::VERBOSITY_QUIET !== $output->getVerbosity()) {
                $output->writeln($mess);
            }
            return;
        }
        foreach ($fileNames as $fileName) {
            if (false === $sqlStatements = $this->safeFileRead($fileName)) {
                $mess = sprintf('<error>Could NOT get contents of SQL file %s</error>', $fileName);
                $yem->triggerLogEvent('Yapeal.Log.log', Logger::INFO, strip_tags($mess));
                $output->writeln($mess);
                throw new YapealDatabaseException(strip_tags($mess), 2);
            }
            $this->executeSqlStatements($sqlStatements, $fileName, $output);
        }
    }
    /**
     * @param OutputInterface $output
     *
     * @return string
     * @throws YapealDatabaseException
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    private function getLatestDatabaseVersion(OutputInterface $output): string
    {
        $sql = $this->getCsq()
            ->getLatestYapealSchemaVersion();
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, 'sql - ' . $sql);
        $version = '19700101000001.000';
        try {
            $result = $this->getPdo()
                ->query($sql, \PDO::FETCH_NUM);
            $version = (string)$result->fetchColumn();
            $result->closeCursor();
        } catch (\PDOException $exc) {
            $sql = '<comment>' . $sql . '</comment>';
            $mess = sprintf('<error>Could NOT query latest database version. Aborting ...</error>', $version);
            if ($output::VERBOSITY_QUIET !== $output->getVerbosity()) {
                $output->writeln([$sql, $mess]);
            }
            throw new YapealDatabaseException(strip_tags($mess), 2);
        }
        return $version;
    }
    /**
     * @param string $latestVersion
     *
     * @return array|string[]
     */
    private function getUpdateFileNames(string $latestVersion): array
    {
        $fileExt = sprintf('.%s.sql', $this->platform);
        $globPath = sprintf('{%1$s}Updates/{*%2$s,*/*%2$s}',
            implode(',', $this->updateDirs),
            $fileExt);
        $regex = '%^.+?/\d{14}\.\d{3}\..+$%';
        $filteredNames = array_filter(preg_grep($regex, glob($globPath, GLOB_BRACE | GLOB_NOESCAPE)),
            function (string $fileName) use ($fileExt, $latestVersion) {
                return $latestVersion < basename($fileName, $fileExt);
            });
        return $filteredNames;
    }
    /**
     * @var string $platform
     */
    private $platform;
    /**
     * @var string[] $updateDirs
     */
    private $updateDirs;
}
