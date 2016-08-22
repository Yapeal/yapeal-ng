<?php
declare(strict_types = 1);
/**
 * Contains trait ActiveTrait.
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
namespace Yapeal\EveApi;

use PDO;
use PDOException;
use Yapeal\Log\Logger;
use Yapeal\Xml\EveApiReadWriteInterface;

/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
/**
 * Trait ActiveTrait.
 *
 * @method \Yapeal\Sql\CommonSqlQueries getCsq()
 * @method \Yapeal\Event\MediatorInterface getYem()
 * @method \PDO getPdo()
 * @method int getMask()
 * @method string createEveApiMessage($messagePrefix, EveApiReadWriteInterface $data)
 */
trait ActiveTrait
{
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @return array
     * @throws \LogicException
     */
    protected function getActive(EveApiReadWriteInterface $data)
    {
        switch (strtolower($data->getEveApiSectionName())) {
            case 'account':
                if ('APIKeyInfo' === $data->getEveApiName()) {
                    $sql = $this->getCsq()
                        ->getActiveRegisteredKeys();
                    break;
                }
                $sql = $this->getCsq()
                    ->getActiveRegisteredAccountStatus($this->getMask());
                break;
            case 'char':
                $sql = $this->getCsq()
                    ->getActiveRegisteredCharacters($this->getMask());
                break;
            case 'corp':
                if ('StarbaseDetails' === $data->getEveApiName()) {
                    $sql = $this->getCsq()
                        ->getActiveStarbaseTowers($this->getMask(), $data->getEveApiArgument('corporationID'));
                    break;
                }
                $sql = $this->getCsq()
                    ->getActiveRegisteredCorporations($this->getMask());
                break;
            default:
                return [];
        }
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $sql);
        try {
            return $this->getPdo()
                ->query($sql)
                ->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $exc) {
            $mess = 'Could NOT get a list of active owners for';
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log',
                    Logger::WARNING,
                    $this->createEveApiMessage($mess, $data),
                    ['exception' => $exc]);
            return [];
        }
    }
}
