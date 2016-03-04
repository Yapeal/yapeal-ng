-- Sql/Eve/CreateRefTypes.sql
-- version 20160201053948.332
CREATE TABLE "{database}"."{table_prefix}eveRefTypes" (
    "refTypeID"   BIGINT(20) UNSIGNED NOT NULL,
    "refTypeName" CHAR(100)           NOT NULL,
    PRIMARY KEY ("refTypeID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160201053948.332')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
