<?php
declare(strict_types = 1);
/**
 * Contains class YapealSetup.
 *
 * PHP version 7.0+
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2016 Michael Cummings
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
 * @author    Michael Cummings <mgcummings@yahoo.com>
 * @copyright 2016 Michael Cummings
 * @license   LGPL-3.0+
 */
namespace Yapeal\Cli\Yapeal;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Yapeal\Cli\ConfigFileTrait;
use Yapeal\Cli\VerbosityMappingTrait;
use Yapeal\CommonToolsTrait;
use Yapeal\Configuration\YamlConfigFile;
use Yapeal\Container\ContainerInterface;
use Yapeal\Event\EveApiEventEmitterTrait;
use Yapeal\Event\YEMAwareInterface;
use Yapeal\Sql\CommonSqlQueries;

/**
 * Class YapealSetup.
 */
class YapealSetup extends Command implements YEMAwareInterface
{
    use CommonToolsTrait, ConfigFileTrait, EveApiEventEmitterTrait, VerbosityMappingTrait;
    /**
     * @param string             $name
     * @param ContainerInterface $dic
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function __construct(string $name, ContainerInterface $dic)
    {
        $this->setDescription('Used to setup Yapeal-ng after Composer require or to interactively do so any time');
        $this->setName($name);
        $this->setDic($dic);
        parent::__construct($name);
    }
    /**
     * Configures the current command.
     */
    public function configure()
    {
        $help = <<<'HELP'
The <info>%command.name%</info> command is an interactive command that can be used
to setup Yapeal-ng in an application after being add to the application's
`composer.json` file. It will analyze where it is being run from and then ask
interactive questions and use their responses to customize an yapeal.yaml file
with their settings. It can also be used to run some of the additional console
commands if desired like Schema:Creator or Schema:Update etc.
HELP;
        $this->addOption('askInteractive',
            'a|i',
            InputOption::VALUE_NONE,
            'Ask interactive questions instead of using multiple choice menus');
        $this->addConfigFileOption();
        $this->setHelp($help);
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
     * @return int 0 if everything went fine, or an error code
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException When this abstract method is not implemented
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Console\Exception\LogicException
     * @throws \Symfony\Component\Console\Exception\RuntimeException
     * @throws \UnexpectedValueException
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$input->isInteractive()) {
            $mess = sprintf('<comment>%1$s can not be used in a non-interactive way be sure not to use'
                . ' the -n, --no-interaction option</comment>',
                $this->getName());
            $output->writeln($mess);
            return 1;
        }
        $this->applyVerbosityMap($output);
        $options = $input->getOptions();
        if (!empty($options['configFile'])) {
            $this->processConfigFile($options['configFile'], $this->getDic());
        }
        if (!$options['askInteractive']) {
            $this->mainMenu($input, $output);
        } else {
            $this->startInteractive($input, $output);
        }
        return 0;
    }
    /** @noinspection PhpMissingParentCallCommonInspection */
    /**
     * @param string $option
     * @param string $text
     */
    private function addMenuChoice(string $option, string $text)
    {
        $this->menuChoices[$option] = $text;
    }
    /**
     * @return string[]
     * @throws \LogicException
     */
    private function getMenuChoices(): array
    {
        if (null === $this->menuChoices) {
            $mess = 'Trying to access menuChoices before it was set';
            throw new \LogicException($mess);
        }
        return $this->menuChoices;
    }
    /**
     * @return bool
     * @throws \LogicException
     */
    private function hasConfigFile(): bool
    {
        if (null === $this->configFile) {
            $this->configFile = false;
            $dic = $this->getDic();
            /** @noinspection PhpParamsInspection */
            if (array_key_exists('Yapeal.vendorParentDir', $dic)) {
                $appConfigFile = $dic['Yapeal.vendorParentDir'] . 'config/yapeal.yaml';
                $this->configFile = is_readable($appConfigFile) && is_file($appConfigFile);
            }
        }
        return $this->configFile;
    }
    /**
     * @return bool
     * @throws \LogicException
     */
    private function hasSchema(): bool
    {
        if (null === $this->schema) {
            $this->schema = true;
            /**
             * @var ContainerInterface|array        $dic
             * @var \Yapeal\Sql\ConnectionInterface $pdo
             * @var CommonSqlQueries                $csq
             */
            $dic = $this->getDic();
            $result = false;
            $sql = $this->getCsq()
                ->getSchemaNames();
            try {
                $pdo = $dic['Yapeal.Sql.Callable.Connection'];
                $result = $pdo->query($sql);
            } catch (\PDOException $exc) {
                $this->schema = false;
            }
            if (false === $result) {
                $this->schema = false;
            }
            $result = array_values($result->fetchAll(\PDO::FETCH_COLUMN));
            $schemaName = $dic['Yapeal.Sql.Platforms.' . $dic['Yapeal.Sql.platform'] . '.schema'];
            if (!in_array($schemaName, $result, false)) {
                $this->schema = false;
            }
        }
        return $this->schema;
    }
    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \LogicException
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Console\Exception\LogicException
     * @throws \Symfony\Component\Console\Exception\RuntimeException
     */
    private function mainMenu(InputInterface $input, OutputInterface $output)
    {
        $this->resetMenu();
        $this->addMenuChoice('1', 'Switch from menus to asking interactive questions');
        $text = 'Create/edit application yapeal.yaml file';
        $this->addMenuChoice('2', $text);
        $text = 'Create/update Yapeal-ng schema';
        $this->addMenuChoice('3', $text);
        $this->addMenuChoice('x', 'Exit command');
        $prompt = 'What do you want to do?(x)';
        /**
         * @var QuestionHelper $question
         */
        $question = $this->getHelper('question');
        $choice = new ChoiceQuestion($prompt, $this->getMenuChoices(), 'x');
        $answer = $question->ask($input, $output, $choice);
        switch ($answer) {
            case 'x':
                break;
            case '1': // Switch from menus to asking interactive questions
                break;
            case '2': // Create/update yapeal.yaml file
                $this->yamlMenu($input, $output);
                break;
            case '3': // Create/update schema
                break;
            default:
                break;
        }
    }
    private function resetMenu()
    {
        $this->menuChoices = [];
    }
    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    private function startInteractive(InputInterface $input, OutputInterface $output)
    {
    }
    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \LogicException
     */
    private function yamlMenu(InputInterface $input, OutputInterface $output)
    {
        if ($this->hasConfigFile()) {
            $ycf = new YamlConfigFile($this->getDic()['Yapeal.vendorParentDir'] . 'config');
            $this->yamlFile = $ycf->read()
                ->flattenYaml();
        }
    }
    /**
     * @var bool $configFile
     */
    private $configFile;
    /**
     * @var string[] $menuChoices
     */
    private $menuChoices;
    /**
     * @var bool $schema
     */
    private $schema;
    /**
     * @var array $yamlFile
     */
    private $yamlFile;
}

