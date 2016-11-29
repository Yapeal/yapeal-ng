-- Sql/Corp/CreateMemberSecurityLog.sql
-- version 20160629053440.098
CREATE TABLE "{schema}"."{tablePrefix}corpMemberSecurityLog" (
    "changeTime"       DATETIME            NOT NULL,
    "characterID"      BIGINT(20) UNSIGNED NOT NULL,
    "issuerID"         BIGINT(20) UNSIGNED NOT NULL,
    "ownerID"          BIGINT(20) UNSIGNED NOT NULL,
    "roleLocationType" CHAR(100)           NOT NULL,
    PRIMARY KEY ("ownerID", "changeTime")
);
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}utilDatabaseVersion" ("version")
    VALUES ('20160629053440.098')
    ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
