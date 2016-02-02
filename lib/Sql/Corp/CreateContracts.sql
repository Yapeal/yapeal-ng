-- Sql/Corp/CreateContracts.sql
-- version 20160201053356.150
CREATE TABLE "{database}"."{table_prefix}corpContracts" (
    "acceptorID" BIGINT(20) UNSIGNED NOT NULL,
    "assigneeID" BIGINT(20) UNSIGNED NOT NULL,
    "availability" CHAR(8) DEFAULT '',
    "buyout" DECIMAL(17, 2) NOT NULL,
    "collateral" DECIMAL(17, 2) NOT NULL,
    "contractID" BIGINT(20) UNSIGNED NOT NULL,
    "dateAccepted" DATETIME DEFAULT '1970-01-01 00:00:01',
    "dateCompleted" DATETIME DEFAULT '1970-01-01 00:00:01',
    "dateExpired" DATETIME NOT NULL DEFAULT '1970-01-01 00:00:01',
    "dateIssued" DATETIME NOT NULL DEFAULT '1970-01-01 00:00:01',
    "endStationID" BIGINT(20) UNSIGNED NOT NULL,
    "forCorp" TINYINT(1) NOT NULL,
    "issuerCorpID" BIGINT(20) UNSIGNED NOT NULL,
    "issuerID" BIGINT(20) UNSIGNED NOT NULL,
    "numDays" BIGINT(20) UNSIGNED NOT NULL,
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "price" DECIMAL(17, 2) NOT NULL,
    "reward" DECIMAL(17, 2) NOT NULL,
    "startStationID" BIGINT(20) UNSIGNED NOT NULL,
    "status" VARCHAR(255) DEFAULT '',
    "title" VARCHAR(255) DEFAULT '',
    "type" CHAR(25) DEFAULT '',
    "volume" DOUBLE NOT NULL,
    PRIMARY KEY ("ownerID","contractID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
('20160201053356.150')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
