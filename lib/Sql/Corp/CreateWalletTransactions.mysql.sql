-- Sql/Corp/CreateWalletTransactions.sql
-- version 20160629053501.871
CREATE TABLE "{schema}"."{tablePrefix}corpWalletTransactions" (
    "characterID"          BIGINT(20) UNSIGNED              NOT NULL,
    "characterName"        CHAR(100)                        NOT NULL,
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
START TRANSACTION;
INSERT INTO "{schema}"."{tablePrefix}utilDatabaseVersion" ("version")
VALUES ('20160629053501.871')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
