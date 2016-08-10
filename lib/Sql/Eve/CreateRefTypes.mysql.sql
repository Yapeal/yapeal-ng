-- Sql/Eve/CreateRefTypes.sql
-- version 20160629053442.214
CREATE TABLE "{schema}"."{tablePrefix}eveRefTypes" (
    "refTypeID"   SMALLINT(5) UNSIGNED NOT NULL,
    "refTypeName" CHAR(100)            NOT NULL,
    PRIMARY KEY ("refTypeID")
);
START TRANSACTION;
INSERT INTO "{schema}"."{tablePrefix}utilDatabaseVersion" ("version")
VALUES ('20160629053442.214')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
