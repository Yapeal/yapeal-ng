-- Sql/Corp/CreateMedals.sql
-- version 20160629053439.047
CREATE TABLE "{schema}"."{tablePrefix}corpMedals" (
    "created"     DATETIME            NOT NULL,
    "creatorID"   BIGINT(20) UNSIGNED NOT NULL,
    "description" TEXT                NOT NULL,
    "medalID"     BIGINT(20) UNSIGNED NOT NULL,
    "ownerID"     BIGINT(20) UNSIGNED NOT NULL,
    "title"       CHAR(255) DEFAULT '',
    PRIMARY KEY ("ownerID", "medalID")
);
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}utilDatabaseVersion" ("version")
    VALUES ('20160629053439.047')
    ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
