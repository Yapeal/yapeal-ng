<?php
declare(strict_types = 1);
/**
 * Contains Yapeal class.
 *
 * PHP version 7.0+
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2014-2017 Michael Cummings
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
 * @copyright 2014-2017 Michael Cummings
 * @license   LGPL-3.0+
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal;

use Yapeal\Event\EveApiEventEmitterTrait;
use Yapeal\Event\MediatorInterface;
use Yapeal\Log\Logger;
use Yapeal\Sql\CommonSqlQueries;
use Yapeal\Sql\CSQAwareTrait;
use Yapeal\Sql\PDOAwareTrait;
use Yapeal\Sql\ConnectionInterface;
use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Class Yapeal
 */
class Yapeal
{
    use CSQAwareTrait;
    use PDOAwareTrait;
    use EveApiEventEmitterTrait;
    /**
     * @param CommonSqlQueries         $csq
     * @param EveApiReadWriteInterface $data
     * @param ConnectionInterface      $pdo
     * @param MediatorInterface        $yem
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        CommonSqlQueries $csq,
        EveApiReadWriteInterface $data,
        ConnectionInterface $pdo,
        MediatorInterface $yem
    ) {
        $this->setCsq($csq);
        $this->data = $data;
        $this->setPdo($pdo);
        $this->setYem($yem);
    }
    /**
     * Starts Eve API processing
     *
     * @return int Returns 0 if everything was fine else something >= 1 for any
     * errors.
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    public function autoMagic()
    {
        $mess = 'Let the magic begin!';
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::INFO, $mess);
        $sql = $this->getCsq()
            ->getActiveApis();
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, 'sql - ' . $sql);
        try {
            $records = $this->getPdo()
                ->query($sql)
                ->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $exc) {
            $mess = 'Could not access yapealEveApi table';
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log', Logger::CRITICAL, $mess, ['exception' => $exc]);
            return 1;
        }
        // Always check APIKeyInfo first.
        array_unshift($records, ['apiName' => 'APIKeyInfo', 'interval' => 300, 'sectionName' => 'account']);
        foreach ($records as $record) {
            $data = clone $this->data;
            $data->setEveApiName($record['apiName'])
                ->setEveApiSectionName($record['sectionName'])
                ->setCacheInterval((int)$record['interval']);
            $this->emitEvents($data, 'start');
        }
        return 0;
    }
    /**
     * @var EveApiReadWriteInterface $data
     */
    private $data;
}
