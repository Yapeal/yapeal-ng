<?php
/**
 * Contains DatabaseInitializer class.
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

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Yapeal\Container\ContainerInterface;

/**
 * Class DatabaseInitializer
 */
class DatabaseInitializer extends AbstractDatabaseCommon
{
    /**
     * @param string|null        $name
     * @param ContainerInterface $dic
     *
     * @throws \Symfony\Component\Console\Exception\LogicException
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    public function __construct($name, ContainerInterface $dic)
    {
        $this->setDescription('Retrieves SQL from files and initializes database');
        $this->setName($name);
        $this->setDic($dic);
        parent::__construct($name);
    }
    /** @noinspection PhpMissingParentCallCommonInspection */
    /**
     * Configures the current command.
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
        $desc = 'Drop existing database before re-creating. <comment>Warning all the tables will be dropped as well!</comment>';
        $this->addOption('dropDatabase', null, InputOption::VALUE_NONE, $desc);
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
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Console\Exception\LogicException
     * @throws \Symfony\Component\Console\Exception\RuntimeException
     * @throws \Yapeal\Exception\YapealDatabaseException
     * @throws \Yapeal\Exception\YapealException
     *
     * @see    setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->processCliOptions($input);
        if ($input->getOption('dropDatabase')) {
            /**
             * @var QuestionHelper $question
             */
            $question = $this->getHelper('question');
            $mess = '<comment>Are you sure you want to drop the database and it\'s tables with their data?(no)</comment>';
            $confirm = new ConfirmationQuestion($mess, false);
            $this->dropDatabase = $question->ask($input, $output, $confirm);
            if (!$this->dropDatabase) {
                $output->writeln('<info>Ignoring drop database</info>');
            }
        }
        return $this->processSql($output);
    }
    /**
     * @param OutputInterface $output
     *
     * @return string[]
     * @throws \LogicException
     */
    protected function getCreateFileList(OutputInterface $output)
    {
        $dic = $this->getDic();
        $sections = ['Database', 'Util', 'Account', 'Api', 'Char', 'Corp', 'Eve', 'Map', 'Server'];
        $path = $dic['Yapeal.Sql.dir'];
        if (!is_readable($path)) {
            if ($output::VERBOSITY_QUIET !== $output->getVerbosity()) {
                $mess = sprintf('<comment>Could NOT access Sql directory %1$s</comment>', $path);
                $output->writeln($mess);
            }
            return [];
        }
        $fileList = [];
        foreach ($sections as $dir) {
            if ('Database' === $dir && $this->dropDatabase) {
                // Add drop database file if requested.
                $fileList[] = $this->getFpn()
                    ->normalizeFile($path . $dir . '/DropDatabase.sql');
            }
            foreach (new \DirectoryIterator($path . $dir . '/') as $fileInfo) {
                // Add file path if it's a sql create file.
                if ($fileInfo->isFile()
                    && 'sql' === $fileInfo->getExtension()
                    && 0 === strpos($fileInfo->getBasename(), 'Create')
                ) {
                    $fileList[] = $this->getFpn()
                        ->normalizeFile($fileInfo->getPathname());
                }
            }
        }
        $fileNames = '%1$sCreateCustomTables.sql,%2$sconfig/CreateCustomTables.sql';
        $vendorPath = '';
        if (!empty($dic['Yapeal.vendorParentDir'])) {
            $fileNames .= ',%3$sconfig/CreateCustomTables.sql';
            $vendorPath = $dic['Yapeal.vendorParentDir'];
        }
        /**
         * @var array $customFiles
         */
        $customFiles = explode(',', sprintf($fileNames, $path, $dic['Yapeal.baseDir'], $vendorPath));
        foreach ($customFiles as $fileName) {
            if (!is_readable($fileName) || !is_file($fileName)) {
                continue;
            }
            $fileList[] = $fileName;
        }
        return $fileList;
    }
    /**
     * @param OutputInterface $output
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Symfony\Component\Console\Exception\LogicException
     * @throws \Yapeal\Exception\YapealDatabaseException
     */
    protected function processSql(OutputInterface $output)
    {
        foreach ($this->getCreateFileList($output) as $fileName) {
            $sqlStatements = file_get_contents($fileName);
            if (false === $sqlStatements) {
                if ($output::VERBOSITY_QUIET !== $output->getVerbosity()) {
                    $mess = sprintf('<error>Could NOT get contents of SQL file %1$s</error>', $fileName);
                    $output->writeln($mess);
                }
                continue;
            }
            $this->executeSqlStatements($sqlStatements, $fileName, $output);
        }
    }
    /**
     * @var bool $dropDatabase
     */
    private $dropDatabase = false;
}
