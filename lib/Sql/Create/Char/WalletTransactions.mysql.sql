-- Sql/Char/CreateWalletTransactions.sql
-- version 20160629053501.276
CREATE TABLE "{schema}"."{tablePrefix}charWalletTransactions" (
    "clientID"             BIGINT(20) UNSIGNED              NOT NULL,
    "clientName"           CHAR(100)                        NOT NULL,
    "clientTypeID"         BIGINT(20) UNSIGNED              NOT NULL,
    "journalTransactionID" BIGINT(20) UNSIGNED              NOT NULL,
    "ownerID"              BIGINT(20) UNSIGNED              NOT NULL,
    "price"                DECIMAL(17, 2)                   NOT NULL,
    "quantity"             BIGINT(20) UNSIGNED              NOT NULL,
    "stationID"            BIGINT(20) UNSIGNED              NOT NULL,
    "stationName"          CHAR(100)                        NOT NULL,
    "transactionDateTime"  DATETIME                         NOT NULL,
    "transactionFor"       ENUM ('corporation', 'personal') NOT NULL,
    "transactionID"        BIGINT(20) UNSIGNED              NOT NULL,
    "transactionType"      ENUM ('buy', 'sell')             NOT NULL,
    "typeID"               BIGINT(20) UNSIGNED              NOT NULL,
    "typeName"             CHAR(100)                        NOT NULL,
    PRIMARY KEY ("ownerID", "transactionID")
);
-- Used altered index name(s) since they get copied to corp table during create ... like ...
ALTER TABLE "{schema}"."{tablePrefix}charWalletTransactions"
    ADD INDEX "WalletTransactions1" ("ownerID", "stationID", "transactionID");
ALTER TABLE "{schema}"."{tablePrefix}charWalletTransactions"
    ADD INDEX "WalletTransactions2" ("ownerID", "typeID", "transactionID");
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}utilDatabaseVersion" ("version")
    VALUES ('20160629053501.276')
    ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
