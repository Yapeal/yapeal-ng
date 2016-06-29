-- Sql/Eve/CreateErrorList.sql
-- version 20160629053419.498
CREATE TABLE "{database}"."{table_prefix}eveErrorList" (
    "errorCode" SMALLINT(4) UNSIGNED NOT NULL,
    "errorText" VARCHAR(255) DEFAULT '',
    PRIMARY KEY ("errorCode")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
('20160629053419.498')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
