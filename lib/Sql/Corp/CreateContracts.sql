-- Sql/Corp/CreateContracts.sql
-- version 20160629053418.719
CREATE TABLE "{database}"."{table_prefix}corpContracts" (
    "acceptorID" BIGINT(20) UNSIGNED NOT NULL,
    "assigneeID" BIGINT(20) UNSIGNED NOT NULL,
    "availability" VARCHAR(255) DEFAULT '',
    "buyout" VARCHAR(255) DEFAULT '',
    "collateral" VARCHAR(255) DEFAULT '',
    "contractID" BIGINT(20) UNSIGNED NOT NULL,
    "dateAccepted" DATETIME NOT NULL DEFAULT '1970-01-01 00:00:01',
    "dateCompleted" DATETIME NOT NULL DEFAULT '1970-01-01 00:00:01',
    "dateExpired" DATETIME NOT NULL DEFAULT '1970-01-01 00:00:01',
    "dateIssued" DATETIME NOT NULL DEFAULT '1970-01-01 00:00:01',
    "endStationID" BIGINT(20) UNSIGNED NOT NULL,
    "forCorp" VARCHAR(255) DEFAULT '',
    "issuerCorpID" BIGINT(20) UNSIGNED NOT NULL,
    "issuerID" BIGINT(20) UNSIGNED NOT NULL,
    "numDays" VARCHAR(255) DEFAULT '',
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "price" VARCHAR(255) DEFAULT '',
    "reward" VARCHAR(255) DEFAULT '',
    "startStationID" BIGINT(20) UNSIGNED NOT NULL,
    "status" VARCHAR(255) DEFAULT '',
    "title" VARCHAR(255) DEFAULT '',
    "type" VARCHAR(255) DEFAULT '',
    "volume" VARCHAR(255) DEFAULT '',
    PRIMARY KEY ("ownerID","contractID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
('20160629053418.719')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
