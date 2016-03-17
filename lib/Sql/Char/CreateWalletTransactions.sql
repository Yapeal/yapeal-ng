-- Sql/Char/CreateWalletTransactions.sql
-- version 20160201053952.926
CREATE TABLE "{database}"."{table_prefix}charWalletTransactions" (
    "accountKey"           SMALLINT(5) UNSIGNED NOT NULL,
    "clientID"             BIGINT(20) UNSIGNED  NOT NULL,
    "clientName"           CHAR(100)            NOT NULL,
    "clientTypeID"         BIGINT(20) UNSIGNED  NOT NULL,
    "journalTransactionID" BIGINT(20) UNSIGNED  NOT NULL,
    "ownerID"              BIGINT(20) UNSIGNED  NOT NULL,
    "price"                DECIMAL(17, 2)       NOT NULL,
    "quantity"             BIGINT(20) UNSIGNED  NOT NULL,
    "stationID"            BIGINT(20) UNSIGNED  NOT NULL,
    "stationName"          CHAR(100)            NOT NULL,
    "transactionDateTime"  DATETIME             NOT NULL,
    "transactionFor"       CHAR(12)             NOT NULL DEFAULT 'corporation',
    "transactionID"        BIGINT(20) UNSIGNED  NOT NULL,
    "transactionType"      CHAR(4)              NOT NULL DEFAULT 'sell',
    "typeID"               BIGINT(20) UNSIGNED  NOT NULL,
    "typeName"             CHAR(100)            NOT NULL,
    PRIMARY KEY ("ownerID", "accountKey", "transactionID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160201053952.926')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
