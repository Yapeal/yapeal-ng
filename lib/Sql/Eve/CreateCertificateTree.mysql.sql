-- Sql/Eve/CreateCertificateTree.sql
-- version 20160629053413.578
CREATE TABLE "{schema}"."{tablePrefix}eveCertificateTree" (
    "categoryID"   BIGINT(20) UNSIGNED NOT NULL,
    "categoryName" CHAR(100)           NOT NULL,
    PRIMARY KEY ("categoryID")
);
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}utilDatabaseVersion" ("version")
    VALUES ('20160629053413.578')
    ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
