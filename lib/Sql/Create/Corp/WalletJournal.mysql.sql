-- Sql/Create/Corp/WalletJournal.sql
-- version 20161202044339.043
CREATE TABLE "{schema}"."{tablePrefix}corpWalletJournal" LIKE "{schema}"."{tablePrefix}charWalletJournal";
ALTER TABLE "{schema}"."{tablePrefix}corpWalletJournal"
    DROP COLUMN "taxAmount";
ALTER TABLE "{schema}"."{tablePrefix}corpWalletJournal"
    DROP COLUMN "taxReceiverID";
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161202044339.043');
COMMIT;
