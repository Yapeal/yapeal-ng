-- Sql/Corp/CreateMemberSecurityLog.sql
-- version 20160201053946.480
CREATE TABLE "{database}"."{table_prefix}corpMemberSecurityLog" (
    "changeTime"       DATETIME            NOT NULL DEFAULT '1970-01-01 00:00:01',
    "characterID"      BIGINT(20) UNSIGNED NOT NULL,
    "issuerID"         BIGINT(20) UNSIGNED NOT NULL,
    "ownerID"          BIGINT(20) UNSIGNED NOT NULL,
    "roleLocationType" VARCHAR(255)                 DEFAULT '',
    PRIMARY KEY ("ownerID", "changeTime")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160201053946.480')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
