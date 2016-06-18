<?php
/**
 * Contains WalletJournal class.
 *
 * PHP version 5.4
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
 * <http://www.gnu.org/licenses/>.
 *
 * You should be able to find a copy of this license in the LICENSE.md file. A
 * copy of the GNU GPL should also be available in the GNU-GPL.md file.
 *
 * @copyright 2016 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\EveApi\Corp;

use Yapeal\EveApi\AccountKeyTrait;
use Yapeal\EveApi\CommonEveApiTrait;

/**
 * Class WalletJournal
 */
class WalletJournal extends CorpSection
{
    use CommonEveApiTrait, AccountKeyTrait {
        AccountKeyTrait::oneShot insteadof CommonEveApiTrait;
        AccountKeyTrait::startEveApi insteadof CommonEveApiTrait;
        CommonEveApiTrait::oneShot as cEATOneShot;
        CommonEveApiTrait::startEveApi as cEATStartEveApi;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->mask = 1048576;
        $this->accountKeys = ['10000', '1000', '1001', '1002', '1003', '1004', '1005', '1006'];
    }
    /**
     * @param string $xml
     * @param string $ownerID
     * @param string $accountKey
     *
     * @return self Fluent interface.
     * @throws \LogicException
     */
    protected function preserveToWallet($xml, $ownerID, $accountKey)
    {
        $tableName = 'corpWalletJournal';
        $columnDefaults = [
            'accountKey'   => $accountKey,
            'amount'       => null,
            'argID1'       => null,
            'argName1'     => '',
            'balance'      => '0.0',
            'date'         => '1970-01-01 00:00:01',
            'owner1TypeID' => null,
            'owner2TypeID' => null,
            'ownerID'      => $ownerID,
            'ownerID1'     => null,
            'ownerID2'     => null,
            'ownerName1'   => '',
            'ownerName2'   => '',
            'reason'       => null,
            'refID'        => null,
            'refTypeID'    => null
        ];
        $this->attributePreserveData($xml, $columnDefaults, $tableName, '//entries/row');
        return $this;
    }
}
