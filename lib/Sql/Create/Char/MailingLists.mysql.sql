-- Sql/Char/CreateMailingLists.sql
-- version 20160629053436.736
CREATE TABLE "{schema}"."{tablePrefix}charMailingLists" (
    "displayName" CHAR(100)           NOT NULL,
    "listID"      BIGINT(20) UNSIGNED NOT NULL,
    "ownerID"     BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID", "listID")
);
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}utilDatabaseVersion" ("version")
    VALUES ('20160629053436.736')
    ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
