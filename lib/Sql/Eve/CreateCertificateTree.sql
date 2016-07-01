-- Sql/Eve/CreateCertificateTree.sql
-- version 20160629053413.578
CREATE TABLE "{database}"."{table_prefix}eveCertificateTree" (
    "categoryID"   BIGINT(20) UNSIGNED NOT NULL,
    "categoryName" CHAR(100)           NOT NULL,
    PRIMARY KEY ("categoryID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160629053413.578')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
