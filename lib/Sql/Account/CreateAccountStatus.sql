-- Sql/Account/CreateAccountStatus.sql
-- version 20160629012109.102
CREATE TABLE "{database}"."{table_prefix}accountAccountStatus" (
    "createDate"   DATETIME            NOT NULL,
    "logonCount"   BIGINT(20) UNSIGNED NOT NULL,
    "logonMinutes" BIGINT(20) UNSIGNED NOT NULL,
    "ownerID"      BIGINT(20) UNSIGNED NOT NULL,
    "paidUntil"    DATETIME            NOT NULL DEFAULT '1970-01-01 00:00:01',
    PRIMARY KEY ("ownerID")
);
CREATE TABLE "{database}"."{table_prefix}accountMultiCharacterTraining" (
    "ownerID"     BIGINT(20) UNSIGNED NOT NULL,
    "trainingEnd" DATETIME            NOT NULL,
    PRIMARY KEY ("ownerID", "trainingEnd")
);
CREATE TABLE "{database}"."{table_prefix}accountOffers" (
    "from"        CHAR(100)           NOT NULL,
    "ISK"         DECIMAL(17, 2)      NOT NULL,
    "ownerID"     BIGINT(20) UNSIGNED NOT NULL,
    "offeredDate" DATETIME            NOT NULL,
    "offerID"     BIGINT(20) UNSIGNED NOT NULL,
    "to"          CHAR(100)           NOT NULL,
    PRIMARY KEY ("ownerID", "offerID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160629012109.102')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
