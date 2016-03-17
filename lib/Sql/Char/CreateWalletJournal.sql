-- Sql/Char/CreateWalletJournal.sql
-- version 20160201053952.250
CREATE TABLE "{database}"."{table_prefix}charWalletJournal" (
    "accountKey"    SMALLINT(5) UNSIGNED NOT NULL,
    "amount"        DECIMAL(17, 2)       NOT NULL,
    "argID1"        BIGINT(20) UNSIGNED  NOT NULL,
    "argName1"      CHAR(100)            NOT NULL,
    "balance"       DECIMAL(17, 2)       NOT NULL,
    "date"          DATETIME             NOT NULL,
    "owner1TypeID"  BIGINT(20) UNSIGNED  NOT NULL,
    "owner2TypeID"  BIGINT(20) UNSIGNED  NOT NULL,
    "ownerID"       BIGINT(20) UNSIGNED  NOT NULL,
    "ownerID1"      BIGINT(20) UNSIGNED  NOT NULL,
    "ownerID2"      BIGINT(20) UNSIGNED  NOT NULL,
    "ownerName1"    CHAR(100)            NOT NULL,
    "ownerName2"    CHAR(100)            NOT NULL,
    "reason"        TEXT,
    "refID"         BIGINT(20) UNSIGNED  NOT NULL,
    "refTypeID"     BIGINT(20) UNSIGNED  NOT NULL,
    "taxAmount"     DECIMAL(17, 2)       NOT NULL,
    "taxReceiverID" BIGINT(20) UNSIGNED  NOT NULL,
    PRIMARY KEY ("ownerID", "accountKey", "refID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160201053952.250')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
