-- Sql/Create/Char/WalletJournal.sql
-- version 20161202044339.022
CREATE TABLE "{schema}"."{tablePrefix}charWalletJournal" (
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
    "reason"        SMALLINT(3) UNSIGNED NOT NULL,
    "refID"         BIGINT(20) UNSIGNED  NOT NULL,
    "refTypeID"     SMALLINT(5) UNSIGNED NOT NULL,
    "taxAmount"     DECIMAL(17, 2)       NOT NULL,
    "taxReceiverID" BIGINT(20) UNSIGNED DEFAULT '0',
    PRIMARY KEY ("ownerID", "refID")
);
-- Used altered index name(s) since they get copied to corp table during create ... like ...
ALTER TABLE "{schema}"."{tablePrefix}charWalletJournal"
    ADD INDEX "WalletJournal1" ("ownerID", "argID1", "refID");
ALTER TABLE "{schema}"."{tablePrefix}charWalletJournal"
    ADD INDEX "WalletJournal2" ("ownerID", "refTypeID", "refID");
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161202044339.022');
COMMIT;
