-- Sql/Eve/CreateErrorList.sql
-- version 20160629053419.498
CREATE TABLE "{schema}"."{tablePrefix}eveErrorList" (
    "errorCode" SMALLINT(4) UNSIGNED NOT NULL,
    "errorText" TEXT,
    PRIMARY KEY ("errorCode")
);
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}utilDatabaseVersion" ("version")
    VALUES ('20160629053419.498')
    ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
