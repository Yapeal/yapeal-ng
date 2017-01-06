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
 * Copyright (C) 2016-2017 Michael Cummings
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
 * @copyright 2016-2017 Michael Cummings
 * @license   LGPL-3.0+
 */
namespace Yapeal\Behat;

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use Webmozart\Assert\Assert;
use Yapeal\AdminTools\ManageRegisteredKey;
use Yapeal\Configuration\Wiring;
use Yapeal\Container\Container;
use Yapeal\Container\ContainerInterface;
use Yapeal\Event\MediatorInterface;
use Yapeal\Sql\CommonSqlQueries;
use Yapeal\Sql\ConnectionInterface;

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
        $this->csq = $this->dic['Yapeal.Sql.Callable.CommonQueries'];
        $this->yem = $this->dic['Yapeal.Event.Callable.Mediator'];
    }
    /**
     * @AfterScenario
     */
    public function clearTestRowAfterScenario()
    {
        if (null !== $this->pdo) {
            Assert::false($this->pdo->inTransaction(), 'Still have pending transaction');
            if (is_array($this->key) && array_key_exists('keyID', $this->key)) {
                $sql = $this->csq->getDeleteFromTableWithKeyID('yapealRegisteredKey', $this->key['keyID']);
                Assert::true($this->pdo->beginTransaction(), 'Could not start transaction for test row');
                $stmt = $this->pdo->prepare($sql);
                Assert::true($stmt->execute(), 'Could not upsert test row');
                Assert::true($this->pdo->commit(), 'Could not commit test row');
            }
        }
        $this->key = [];
        $this->tableRow = [];
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
     * @throws \PDOException
     * @throws \UnexpectedValueException
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
     * @When I delete keyID = :keyID in the ManageRegisteredKey class
     *
     * @param int $keyID
     */
    public function iDeleteKeyIDInTheManageRegisteredKeyClass(int $keyID)
    {
        $this->mrk->delete($keyID);
    }
    /**
     * @Given I have an initialized instance of PDO connection
     *
     * @throws \InvalidArgumentException
     */
    public function iHaveAnInitializedInstanceOfPDOConnection()
    {
        $this->pdo = $this->dic['Yapeal.Sql.Callable.Connection'];
    }
    /**
     * @Given I have an new instance of the ManageRegisteredKey class
     */
    public function iHaveAnNewInstanceOfTheManageRegisteredKeyClass()
    {
        $this->mrk = new ManageRegisteredKey($this->csq, $this->pdo, $this->yem);
    }
    /**
     * @When I successfully commit the delete in ManageRegisteredKey
     */
    public function iSuccessfullyCommitTheDeleteInManageRegisteredKey()
    {
        Assert::true($this->mrk->commit(), $this->mrk->getLastErrorString());
    }
    /**
     * @When I successfully commit the new key to the table in ManageRegisteredKey
     */
    public function iSuccessfullyCommitTheNewKeyToTheTableInManageRegisteredKey()
    {
        Assert::true($this->mrk->commit(), $this->mrk->getLastErrorString());
    }
    /**
     * @Given if I set the refresh flag when reading the same keyID in ManageRegisteredKey I get the same data as in the table back.
     */
    public function ifISetTheRefreshFlagWhenReadingTheSameKeyIDInManageRegisteredKeyIGetTheSameDataAsInTheTableBack()
    {
        $expected = json_encode($this->tableRow);
        $result = json_encode($this->mrk->read($this->tableRow['keyID'], true));
        Assert::same($result, $expected);
    }
    /**
     * @Then if I set the refresh flag when reading the same keyID in ManageRegisteredKey I should get: :active :activeAPIMask :keyID :vCode
     *
     * @param bool   $active
     * @param int    $activeAPIMask
     * @param int    $keyID
     * @param string $vCode
     *
     * @throws \InvalidArgumentException
     * @throws \PDOException
     * @throws \UnexpectedValueException
     */
    public function ifISetTheRefreshFlagWhenReadingTheSameKeyIDInManageRegisteredKeyIShouldGet(
        bool $active,
        int $activeAPIMask,
        int $keyID,
        string $vCode
    ) {
        $expected = json_encode(compact('active', 'activeAPIMask', 'keyID', 'vCode'));
        $result = json_encode($this->mrk->read($keyID, true));
        Assert::same($result, $expected);
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
     * @Then the isDirty flag should be cleared in ManageRegisteredKey
     */
    public function theIsDirtyFlagShouldBeClearedInManageRegisteredKey()
    {
        Assert::false($this->mrk->isDirty(), 'The isDirty flag was not cleared');
    }
    /**
     * @Then the isDirty flag should be set in ManageRegisteredKey
     */
    public function theIsDirtyFlagShouldBeSetInManageRegisteredKey()
    {
        Assert::true($this->mrk->isDirty(), 'The isDirty flag was not set');
    }
    /**
     * @Then there should now exist a row in the :tableName table containing: :active :activeAPIMask :keyID :vCode
     * @Then there should still exist a row in the :tableName table containing: :active :activeAPIMask :keyID :vCode
     *
     * @param string $tableName
     * @param string $active
     * @param string $activeAPIMask
     * @param string $keyID
     * @param string $vCode
     *
     * @throws \InvalidArgumentException
     * @throws \PDOException
     */
    public function thereExistsARowInTheTableContaining(
        string $tableName,
        string $active,
        string $activeAPIMask,
        string $keyID,
        string $vCode
    ) {
        $expected = json_encode([compact('active', 'activeAPIMask', 'keyID', 'vCode')]);
        $result = json_encode($this->getTableRowsByIndex($tableName,
            ['active', 'activeAPIMask', 'keyID', 'vCode'],
            ['keyID' => $keyID]));
        Assert::same($result, $expected);
    }
    /**
     * @Given there is an existing row in the yapealRegisteredKey table containing: :active :activeAPIMask :keyID :vCode
     *
     * @param bool   $active
     * @param int    $activeAPIMask
     * @param int    $keyID
     * @param string $vCode
     *
     * @throws \InvalidArgumentException
     * @throws \PDOException
     */
    public function thereIsAnExistingRowInTheTableContaining(
        bool $active,
        int $activeAPIMask,
        int $keyID,
        string $vCode
    ) {
        Assert::false($this->pdo->inTransaction(), 'Still have an existing pending transaction');
        $sql = $this->csq->getUpsert('yapealRegisteredKey', ['active', 'activeAPIMask', 'keyID', 'vCode'], 1);
        Assert::true($this->pdo->beginTransaction(), 'Could not start transaction for test row');
        $stmt = $this->pdo->prepare($sql);
        Assert::true($stmt->execute([$active ?: 0, $activeAPIMask, $keyID, $vCode]), 'Could not upsert test row');
        Assert::true($this->pdo->commit(), 'Could not commit test row');
        $this->tableRow = compact('active', 'activeAPIMask', 'keyID', 'vCode');
    }
    /**
     * @Given there is not a keyID = :keyID row in the yapealRegisteredKey table
     *
     * @param int $keyID
     *
     * @throws \InvalidArgumentException
     * @throws \PDOException
     */
    public function thereIsNotAKeyIDRowInTheTable(int $keyID)
    {
        Assert::false($this->pdo->inTransaction(), 'Still have an existing pending transaction');
        $sql = $this->csq->getDeleteFromTableWithKeyID('yapealRegisteredKey', $keyID);
        Assert::true($this->pdo->beginTransaction(), 'Could not start transaction of test row');
        $stmt = $this->pdo->prepare($sql);
        Assert::true($stmt->execute(), 'Could not execute delete of test row');
        Assert::true($this->pdo->commit(), 'Could not commit delete of test row');
    }
    /**
     * @Then there should still not be a ":primary" = :keyID row in the :tableName table
     * @Then there should no longer be a ":primary" = :keyID row in the :tableName table
     *
     * @param string $primary
     * @param int    $keyID
     * @param string $tableName
     *
     * @throws \InvalidArgumentException
     * @throws \PDOException
     */
    public function thereShouldStillNotBeARowInTheTable(string $primary, int $keyID, string $tableName)
    {
        $mess = 'Found %s unexpected row(s) for keyID in table ' . $tableName;
        Assert::eq($this->getTableRowCount($primary, $keyID, $tableName), 0, $mess);
    }
    /**
     * @param string $primary
     * @param int    $keyID
     * @param string $tableName
     *
     * @return int
     * @throws \InvalidArgumentException
     * @throws \PDOException
     * @uses self::getTableRowsByIndex()
     */
    private function getTableRowCount(string $primary, int $keyID, string $tableName): int
    {
        return count($this->getTableRowsByIndex($tableName, ['*'], [$primary => $keyID]));
    }
    /**
     * @param string $tableName      Plain table name as schema name and prefix will be added automatically.
     * @param array  $columnNameList A simple array of column names or single item array like ['*'].
     * @param array  $where          For example ['keyID' => 123, ...]. Does not have to be part of $columnNameList.
     *
     * @return array Result from PDO::fetchAll() using FETCH_ASSOC option.
     * @throws \InvalidArgumentException
     * @throws \PDOException
     */
    private function getTableRowsByIndex(string $tableName, array $columnNameList, array $where): array
    {
        $sql = $this->csq->getSelect($tableName, $columnNameList, $where);
        $stmt = $this->pdo->query($sql);
        Assert::isInstanceOf($stmt,
            \PDOStatement::class,
            'PDO query did not return instance of PDOStatement check query syntax');
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
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
     * @var ConnectionInterface $pdo
     */
    private $pdo;
    /**
     * @var array $tableRow
     */
    private $tableRow;
    /**
     * @var MediatorInterface $yem
     */
    private $yem;
    /**
     * @Then I can update :changed = :newValue in ManageRegisteredKey but the other columns don't change
     *
     * @param string $changed
     * @param string $newValue
     *
     * @throws \InvalidArgumentException
     * @throws \PDOException
     * @throws \UnexpectedValueException
     */
    public function iCanUpdateInManageRegisteredKeyButTheOtherColumnsDonTChange(string $changed, string $newValue)
    {
        Assert::oneOf($newValue, ['active', 'activeAPIMask', 'vCode']);
        switch ($changed) {
            case 'active':
                $this->mrk->update((bool)$newValue);
                $this->tableRow[$changed] = (bool)$newValue;
                break;
            case 'activeAPIMask':
                $this->mrk->update(null, (int)$newValue);
                $this->tableRow[$changed] = (int)$newValue;
                break;
            case 'vCode':
                $this->mrk->update(null, null, (string)$newValue);
                $this->tableRow[$changed] = (string)$newValue;
                break;
        }
        $expected = json_encode($this->tableRow);
        $result = json_encode($this->mrk->read($this->tableRow['keyID']));
        Assert::same($result, $expected);
    }
    /**
     * @Then I can update :arg1 = :arg2 in ManageRegisteredKey but the other columns don't change:
     */
    public function iCanUpdateInManageRegisteredKeyButTheOtherColumnsDonTChange2($arg1, $arg2, TableNode $table)
    {
        throw new PendingException();
    }
}
