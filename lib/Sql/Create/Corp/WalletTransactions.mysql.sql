-- Sql/Create/Corp/WalletTransactions.sql
-- version 20161202044339.044
CREATE TABLE "{schema}"."{tablePrefix}corpWalletTransactions" LIKE "{schema}"."{tablePrefix}charWalletTransactions";
ALTER TABLE "{schema}"."{tablePrefix}corpWalletTransactions"
    ADD COLUMN "characterName" CHAR(100) NOT NULL
    FIRST;
ALTER TABLE "{schema}"."{tablePrefix}corpWalletTransactions"
    ADD COLUMN "characterID" BIGINT(20) UNSIGNED NOT NULL
    FIRST;
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161202044339.044');
COMMIT;
