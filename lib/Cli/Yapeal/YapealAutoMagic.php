<?php
declare(strict_types = 1);
/**
 * Contains class YapealAutoMagic.
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
 * @copyright 2016 Michael Cummings
 * @license   LGPL-3.0+
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Cli\Yapeal;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yapeal\Cli\ConfigFileTrait;
use Yapeal\Cli\VerbosityToStrategyTrait;
use Yapeal\CommonToolsTrait;
use Yapeal\Container\ContainerInterface;
use Yapeal\Event\EveApiEventEmitterTrait;
use Yapeal\Event\YEMAwareInterface;
use Yapeal\Log\Logger;
use Yapeal\Yapeal;

/**
 * Class YapealAutoMagic.
 */
class YapealAutoMagic extends Command implements YEMAwareInterface
{
    use CommonToolsTrait, ConfigFileTrait, EveApiEventEmitterTrait, VerbosityToStrategyTrait;
    /**
     * @param string|null        $name
     * @param ContainerInterface $dic
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function __construct($name, ContainerInterface $dic)
    {
        $this->setDescription('Auto-magically process all Eve APIs');
        $this->setName($name);
        $this->setDic($dic);
        parent::__construct($name);
    }
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $help = <<<'HELP'
The <info>%command.full_name%</info> command executes the main autoMagic method
of the Yapeal class. This is meant as a replace for the now deprecated
bin/yapeal.php script used to start Yapeal from earlier versions.

    <info>php %command.full_name%</info>

EXAMPLES:
Using with optional --configFile option to override matching existing settings
from other normally processed configuration files.
    <info>%command.name% -c "path/to/my/special/config.yaml"</info>

HELP;
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
     * @return int|null null or 0 if everything went fine, or an error code
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Yapeal\Exception\YapealDatabaseException
     * @throws \Yapeal\Exception\YapealException
     *
     * @see    setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dic = $this->getDic();
        if (!$this->hasYem()) {
            $this->setYem($dic['Yapeal.Event.Mediator']);
        }
        $this->setLogThresholdFromVerbosity($output);
        if ($output::VERBOSITY_QUIET !== $output->getVerbosity()) {
            $mess = sprintf('<info>Starting %1$s</info>', $this->getName());
            $output->writeln($mess);
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log', Logger::INFO, strip_tags($mess));
        }
        $options = $input->getOptions();
        if (!empty($options['configFile'])) {
            $this->processConfigFile($options['configFile'], $dic);
        }
        return (new Yapeal($dic))->autoMagic();
    }
}
