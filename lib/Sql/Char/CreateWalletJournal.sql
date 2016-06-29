-- Sql/Char/CreateWalletJournal.sql
-- version 20160629053500.153
CREATE TABLE "{database}"."{table_prefix}charWalletJournal" (
    "amount" VARCHAR(255) DEFAULT '',
    "argID1" VARCHAR(255) DEFAULT '',
    "argName1" CHAR(100) NOT NULL,
    "balance" DECIMAL(17, 2) NOT NULL,
    "date" DATETIME NOT NULL DEFAULT '1970-01-01 00:00:01',
    "owner1TypeID" BIGINT(20) UNSIGNED NOT NULL,
    "owner2TypeID" BIGINT(20) UNSIGNED NOT NULL,
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "ownerID1" VARCHAR(255) DEFAULT '',
    "ownerID2" VARCHAR(255) DEFAULT '',
    "ownerName1" CHAR(100) NOT NULL,
    "ownerName2" CHAR(100) NOT NULL,
    "reason" VARCHAR(255) DEFAULT '',
    "refID" BIGINT(20) UNSIGNED NOT NULL,
    "refTypeID" BIGINT(20) UNSIGNED NOT NULL,
    "taxAmount" DECIMAL(17, 2) NOT NULL,
    "taxReceiverID" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID","refID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
('20160629053500.153')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
