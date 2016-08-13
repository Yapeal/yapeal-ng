<?php
declare(strict_types = 1);
/**
 * Contains DatabaseInitializer class.
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

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Yapeal\Container\ContainerInterface;
use Yapeal\Event\YEMAwareTrait;
use Yapeal\Log\Logger;

/**
 * Class DatabaseInitializer
 */
class DatabaseInitializer extends AbstractDatabaseCommon
{
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
        $this->setDescription('Retrieves SQL from files and initializes database');
        $this->setName($name);
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
        $this->addOptions($help);
        $desc = 'Drop existing schema(database) before re-creating. <comment>Warning all the tables will be dropped as well!</comment>';
        $this->addOption('dropSchema', null, InputOption::VALUE_NONE, $desc);
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
            $mess = '<comment>Are you sure you want to drop the schema(database) and it\'s tables with their data?(no)</comment>';
            $confirm = new ConfirmationQuestion($mess, false);
            $this->dropSchema = $question->ask($input, $output, $confirm);
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
     * @throws \Yapeal\Exception\YapealDatabaseException
     * @throws \Yapeal\Exception\YapealFileSystemException
     */
    protected function processSql(OutputInterface $output)
    {
        $yem = $this->getYem();
        /**
         * @var array        $section
         * @var string|false $sqlStatements
         */
        foreach ($this->getCreateFileList($output) as $section) {
            foreach ($section as $keyName => $sqlStatements) {
                if (false === $sqlStatements) {
                    if ($output::VERBOSITY_QUIET !== $output->getVerbosity()) {
                        $mess = sprintf('<error>Could NOT get contents of SQL file for %1$s</error>', $keyName);
                        $output->writeln($mess);
                        $yem->triggerLogEvent('Yapeal.Log.log', Logger::INFO, strip_tags($mess));
                    }
                    continue;
                }
                $this->executeSqlStatements($sqlStatements, $keyName, $output);
            }
        }
    }
    /**
     * First custom tables sql file found is returned.
     *
     * @param string $path
     * @param string $platformExt
     * @param array  $fileList
     *
     * @return array
     * @throws \LogicException
     * @throws \Yapeal\Exception\YapealFileSystemException
     */
    private function addCustomFile(string $path, string $platformExt, array $fileList): array
    {
        $fileNames = '%1$sCreateCustomTables,%2$sconfig/CreateCustomTables';
        $subs = [$path, $this->getDic()['Yapeal.baseDir']];
        if (!empty($dic['Yapeal.vendorParentDir'])) {
            $fileNames .= ',%3$sconfig/CreateCustomTables';
            $subs[] = $this->getDic()['Yapeal.vendorParentDir'];
        }
        $customFiles = array_reverse(explode(',', vsprintf($fileNames, $subs)));
        // First one found wins.
        foreach ([$platformExt, '.sql'] as $ext) {
            foreach ($customFiles as $keyName) {
                if (is_readable($keyName . $ext) && is_file($keyName . $ext)) {
                    $contents = $this->safeFileRead($keyName . $ext);
                    if (false === $contents) {
                        continue;
                    }
                    $fileList['Custom'][$keyName] = $contents;
                    break 2;
                }
            }
        }
        return $fileList;
    }
    /**
     * @param OutputInterface $output
     *
     * @return array
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Yapeal\Exception\YapealFileSystemException
     */
    private function getCreateFileList(OutputInterface $output): array
    {
        $dic = $this->getDic();
        $path = $dic['Yapeal.Sql.dir'];
        if (!is_readable($path)) {
            if ($output::VERBOSITY_QUIET !== $output->getVerbosity()) {
                $mess = sprintf('<comment>Could NOT access Sql directory %1$s</comment>', $path);
                $this->getYem()
                    ->triggerLogEvent('Yapeal.Log.log', Logger::INFO, strip_tags($mess));
                $output->writeln($mess);
            }
            return [];
        }
        $fileList = [];
        $platformExt = sprintf('.%1$s.sql', $dic['Yapeal.Sql.platform']);
        // First find any sql files with platform extension.
        $fileList = $this->getSectionsFileList($path, $fileList, $platformExt);
        $fileList = $this->prependDropSchema($path, $fileList, $platformExt);
        $fileList = $this->addCustomFile($path, $platformExt, $fileList);
        return $fileList;
    }
    /**
     * @param string $path
     * @param array  $fileList
     * @param string $ext
     *
     * @return array
     * @throws \Yapeal\Exception\YapealFileSystemException
     */
    private function getSectionsFileList(string $path, array $fileList, string $ext = '.sql'): array
    {
        $fpn = $this->getFpn();
        $sections = ['Schema', 'Util', 'Account', 'Api', 'Char', 'Corp', 'Eve', 'Map', 'Server'];
        foreach ($sections as $section) {
            foreach (new \DirectoryIterator($path . $section . '/') as $fileInfo) {
                // Add file path if it's a sql create file for the correct platform.
                if ($fileInfo->isFile() && 0 === strpos($fileInfo->getBasename(), 'Create')) {
                    $baseName = $fileInfo->getBasename();
                    $firstDot = strpos($baseName, '.');
                    $isSql = $firstDot === strpos($baseName, '.sql');
                    $isPlatform = $firstDot === strpos($baseName, $ext);
                    $baseName = substr($baseName, 0, $firstDot);
                    $keyName = $fpn->normalizePath($fileInfo->getPath()) . $baseName;
                    $notSet = !array_key_exists($section, $fileList)
                        || !array_key_exists($keyName, $fileList[$section])
                        || false === $fileList[$section][$keyName];
                    if ($isPlatform) {
                        $fileList[$section][$keyName] = $this->safeFileRead($keyName . $ext);
                    } elseif ($isSql && $notSet) {
                        $fileList[$section][$keyName] = $this->safeFileRead($keyName . '.sql');
                    }
                }
            }
        }
        return $fileList;
    }
    /**
     * Prepends drop schema if it is requested and exists.
     *
     * @param string $path
     * @param array  $fileList
     * @param string $platformExt
     *
     * @return array
     * @throws \Yapeal\Exception\YapealFileSystemException
     */
    private function prependDropSchema(string $path, array $fileList, string $platformExt)
    {
        if (true !== $this->dropSchema) {
            return $fileList;
        }
        // Add drop database file if requested and exists.
        $keyName = $path . 'Schema/DropSchema';
        foreach ([$platformExt, '.sql'] as $ext) {
            if (is_readable($keyName . $ext) && is_file($keyName . $ext)) {
                $contents = $this->safeFileRead($keyName . $ext);
                if (false === $contents) {
                    continue;
                }
                if (array_key_exists('Schema', $fileList)) {
                    $schema = array_reverse($fileList['Schema'], true);
                    $schema[$keyName] = $contents;
                    $fileList['Schema'] = array_reverse($schema, true);
                    break;
                } else {
                    $fileList = array_reverse($fileList, true);
                    $fileList['Schema'][$keyName] = $contents;
                    $fileList = array_reverse($fileList, true);
                }
            }
        }
        return $fileList;
    }
    /**
     * @var bool $dropSchema
     */
    private $dropSchema = false;
}
