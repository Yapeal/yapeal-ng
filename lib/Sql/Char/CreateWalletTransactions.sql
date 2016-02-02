-- Sql/Char/CreateWalletTransactions.sql
-- version 20160201053952.926
CREATE TABLE "{database}"."{table_prefix}charWalletTransactions" (
    "clientID" BIGINT(20) UNSIGNED NOT NULL,
    "clientName" CHAR(100) NOT NULL,
    "clientTypeID" BIGINT(20) UNSIGNED NOT NULL,
    "journalTransactionID" BIGINT(20) UNSIGNED NOT NULL,
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "price" VARCHAR(255) DEFAULT '',
    "quantity" VARCHAR(255) DEFAULT '',
    "stationID" BIGINT(20) UNSIGNED NOT NULL,
    "stationName" CHAR(100) NOT NULL,
    "transactionDateTime" DATETIME NOT NULL DEFAULT '1970-01-01 00:00:01',
    "transactionFor" VARCHAR(255) DEFAULT '',
    "transactionID" BIGINT(20) UNSIGNED NOT NULL,
    "transactionType" VARCHAR(255) DEFAULT '',
    "typeID" BIGINT(20) UNSIGNED NOT NULL,
    "typeName" CHAR(100) NOT NULL,
    PRIMARY KEY ("ownerID","transactionID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
('20160201053952.926')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
