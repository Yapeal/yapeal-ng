<?php
declare(strict_types = 1);
/**
 * Contains class AdminToolsContext.
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
namespace Yapeal\Behat;

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use Webmozart\Assert\Assert;
use Yapeal\AdminTools\ManageRegisteredKey;
use Yapeal\Configuration\Wiring;
use Yapeal\Container\Container;
use Yapeal\Container\ContainerInterface;
use Yapeal\Event\MediatorInterface;
use Yapeal\Sql\CommonSqlQueries;

/**
 * Class AdminToolsContext.
 */
class AdminToolsContext implements Context
{
    /**
     * AdminToolsContext constructor.
     */
    public function __construct()
    {
        $this->dic = new Container();
        (new Wiring($this->dic))->wireAll();
        $this->csq = $this->dic['Yapeal.Sql.CommonQueries'];
        $this->yem = $this->dic['Yapeal.Event.Mediator'];
    }
    /**
     * @AfterScenario
     */
    public function clearTestRowAfterScenario()
    {
        try {
            $sql = $this->csq->getDeleteFromTableWithKeyID('yapealRegisteredKey', $this->key['keyID']);
            $this->pdo->exec($sql);
        } catch (\Throwable $exc) {
            // Ignore any problems nothing to do about them here.
        }
    }
    /**
     * @Then from the ManageRegisteredKey class I should be able to read back: :active :activeAPIMask :keyID :vCode
     *
     * @param bool   $active
     * @param int    $activeAPIMask
     * @param int    $keyID
     * @param string $vCode
     *
     * @throws \InvalidArgumentException
     */
    public function fromTheManageRegisteredKeyClassIShouldBeAbleToReadBack(
        bool $active,
        int $activeAPIMask,
        int $keyID,
        string $vCode
    ) {
        $expected = json_encode(compact('active', 'activeAPIMask', 'keyID', 'vCode'));
        $result = json_encode($this->mrk->read($this->key['keyID']));
        Assert::same($result, $expected);
    }
    /**
     * @When I create the new key in the ManageRegisteredKey class
     */
    public function iCreateTheNewKeyInTheManageRegisteredKeyClass()
    {
        /**
         * @var  bool  $active
         * @var int    $activeAPIMask
         * @var int    $keyID
         * @var string $vCode
         */
        extract($this->key);
        $this->mrk->create($keyID, $active, $activeAPIMask, $vCode);
    }
    /**
     * @Given I have an initialized instance of PDO connection
     *
     * @throws \InvalidArgumentException
     */
    public function iHaveAnInitializedInstanceOfPDOConnection()
    {
        $this->pdo = $this->dic['Yapeal.Sql.Connection'];
    }
    /**
     * @Given I have an new instance of the ManageRegisteredKey class
     */
    public function iHaveAnNewInstanceOfTheManageRegisteredKeyClass()
    {
        $this->mrk = new ManageRegisteredKey($this->csq, $this->pdo, $this->yem);
    }
    /**
     * @When I successfully commit the new key to the table in ManageRegisteredKey
     */
    public function iSuccessfullyCommitTheNewKeyToTheTableInManageRegisteredKey()
    {
        Assert::true($this->mrk->commit(), $this->mrk->getLastErrorString());
    }
    /**
     * @Given that I have the new key information: :active :activeAPIMask :keyID :vCode
     *
     * @param bool   $active
     * @param int    $activeAPIMask
     * @param int    $keyID
     * @param string $vCode
     */
    public function thatIHaveTheNewKeyInformation(bool $active, int $activeAPIMask, int $keyID, string $vCode)
    {
        $this->key = ['active' => $active, 'activeAPIMask' => $activeAPIMask, 'keyID' => $keyID, 'vCode' => $vCode];
    }
    /**
     * @Then the isDirty flag should be set in ManageRegisteredKey
     */
    public function theIsDirtyFlagShouldBeSetInManageRegisteredKey()
    {
        Assert::true($this->mrk->isDirty());
    }
    /**
     * @Given there is not a keyID = :keyID row in the :tableName table
     * @Then  there should still not be a keyID = :arg1 row in the :arg2 table"
     *
     * @param int    $keyID
     * @param string $tableName
     *
     * @throws \InvalidArgumentException
     */
    public function thereIsNotAKeyIDRowInTheTable(int $keyID, string $tableName)
    {
        $mess = 'Found %s unexpected row(s) for keyID in table ' . $tableName;
        Assert::eq($this->getTableRowCount($keyID, $tableName), 0, $mess);
    }
    /**
     * @param int    $keyID
     * @param string $tableName
     *
     * @return int
     */
    private function getTableRowCount(int $keyID, string $tableName): int
    {
        $sql = sprintf(/** @lang text */
            'select * from "yapeal-ng"."%s" where "keyID"=%s',
            $tableName,
            $keyID);
        $stmt = $this->pdo->query($sql);
        $result = $stmt->rowCount();
        $stmt->closeCursor();
        return $result;
    }
    /**
     * @var CommonSqlQueries $csq
     */
    private $csq;
    /**
     * @var ContainerInterface $dic
     */
    private $dic;
    /**
     * @var array $key
     */
    private $key;
    /**
     * @var ManageRegisteredKey $mrk
     */
    private $mrk;
    /**
     * @var \PDO $pdo
     */
    private $pdo;
    /**
     * @var MediatorInterface $yem
     */
    private $yem;
    /**
     * @Then there should now exist a row in the :arg1 table containing: :arg2 :arg3 :arg4 :arg5
     */
    public function thereShouldNowExistARowInTheTableContaining($arg1, $arg2, $arg3, $arg4, $arg5)
    {
        throw new PendingException();
    }
}
