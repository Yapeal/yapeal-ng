-- Sql/Corp/CreateWalletJournal.sql
-- version 20160629053500.715
CREATE TABLE "{schema}"."{tablePrefix}corpWalletJournal" LIKE "{schema}"."{tablePrefix}charWalletJournal";
ALTER TABLE "{schema}"."{tablePrefix}corpWalletJournal"
    DROP COLUMN "taxAmount";
ALTER TABLE "{schema}"."{tablePrefix}corpWalletJournal"
    DROP COLUMN "taxReceiverID";
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20160629053500.715');
COMMIT;
