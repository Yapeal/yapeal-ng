<?php
declare(strict_types = 1);
/**
 * Contains Wiring class.
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
namespace Yapeal\Configuration;

use Yapeal\Container\ContainerInterface;
use Yapeal\Container\DicAwareInterface;
use Yapeal\Container\DicAwareTrait;

/**
 * Class Wiring
 */
class Wiring implements DicAwareInterface
{
    use DicAwareTrait;
    /**
     * @param ContainerInterface $dic
     */
    public function __construct(ContainerInterface $dic)
    {
        $this->setDic($dic);
    }
    /**
     * This is used to configure/wire all the pieces of Yapeal-ng together.
     *
     * It is not required to use this method or even this class but the rest of Yapeal-ng relies heavily on the
     * container $dic having been setup in a way that mimics it closely. With all the other ways this method and other
     * parts of Yapeal-ng provides to override or change things it is __very strongly__ suggested that application
     * developers use it.
     *
     * _NOTE:_
     *
     *     The Yapeal.Wiring.Classes.config setting has extra special handling because everything else is very heavily
     *     dependant on the initial paths it determines which are used to find everything else. An application developer
     *     considering override this setting should shoot yourself in the foot first to help dull the pain you will be
     *     causing yourself by overriding it. For the masochists that go ahead, enjoy it, but I don't want any pictures
     *     or descriptive e-mail sent to me about your experience please.
     *
     * @return self Fluent interface.
     * @throws \LogicException
     */
    public function wireAll(): self
    {
        $dic = $this->getDic();
        // First things first, should add self to Container and freeze so can't be overwritten later by oops.
        if (empty($dic['Yapeal.Wiring.Callable.Wiring'])) {
            $dic['Yapeal.Wiring.Callable.Wiring'] = function (ContainerInterface $dic) {
                return new Wiring($dic);
            };
            $dic['Yapeal.Wiring.Callable.Wiring'];
        }
        $base = 'Yapeal.Wiring.Classes.';
        $dic[$base . 'config'] = $dic[$base . 'config'] ?? '\Yapeal\Configuration\ConfigWiring';
        $names = ['Config', 'Event', 'Log', 'Sql', 'Xml', 'Xsd', 'Xsl', 'FileSystem', 'Network', 'EveApi'];
        /**
         * @var WiringInterface $class
         */
        foreach ($names as $name) {
            $setting = $base . strtolower($name);
            if (!empty($dic[$setting])
                && is_subclass_of($dic[$setting], WiringInterface::class, true)
            ) {
                $class = new $dic[$setting];
                $class->wire($dic);
                continue;
            }
            $mess = sprintf('Could NOT find mandatory %s wiring class. Aborting ...', $name);
            throw new \LogicException($mess);
        }
        return $this;
    }
}
