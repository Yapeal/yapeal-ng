<?php
/**
 * Contains Yapeal class.
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
namespace Yapeal;

use PDO;
use PDOException;
use Yapeal\Container\ContainerInterface;
use Yapeal\Event\EveApiEventEmitterTrait;
use Yapeal\Exception\YapealDatabaseException;
use Yapeal\Exception\YapealException;
use Yapeal\Log\Logger;
use Yapeal\Sql\CommonSqlQueries;
use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Class Yapeal
 */
class Yapeal
{
    use CommonToolsTrait, EveApiEventEmitterTrait;
    /**
     * @param ContainerInterface $dic
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws YapealException
     * @throws YapealDatabaseException
     * @throws \LogicException
     */
    public function __construct(ContainerInterface $dic)
    {
        $this->setDic($dic);
    }
    /**
     * Starts Eve API processing
     *
     * @return int Returns 0 if everything was fine else something >= 1 for any
     * errors.
     * @throws \LogicException
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function autoMagic()
    {
        $dic = $this->getDic();
        $this->setYem($dic['Yapeal.Event.Mediator']);
        $mess = 'Let the magic begin!';
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::INFO, $mess);
        /**
         * @var CommonSqlQueries $csq
         */
        $csq = $dic['Yapeal.Sql.CommonQueries'];
        $sql = $csq->getActiveApis();
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::INFO, $sql);
        try {
            /**
             * @var PDO $pdo
             */
            $pdo = $dic['Yapeal.Sql.Connection'];
            $records = $pdo->query($sql)
                ->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $exc) {
            $mess = 'Could not access utilEveApi table';
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log', Logger::INFO, $mess, ['exception' => $exc]);
            return 1;
        }
        // Always check APIKeyInfo.
        array_unshift($records, ['apiName' => 'APIKeyInfo', 'interval' => '300', 'sectionName' => 'account']);
        foreach ($records as $record) {
            /**
             * Get new Data instance from factory.
             *
             * @var EveApiReadWriteInterface $data
             */
            $data = $dic['Yapeal.Xml.Data'];
            $data->setEveApiName($record['apiName'])
                ->setEveApiSectionName($record['sectionName'])
                ->setCacheInterval($record['interval']);
            $this->emitEvents($data, 'start');
        }
        return 0;
    }
}
