-- Sql/Eve/CreateCertificateTree.sql
-- version 20160201053352.744
CREATE TABLE "{database}"."{table_prefix}eveCertificateTree" (
    "categoryID"   BIGINT(20) UNSIGNED NOT NULL,
    "categoryName" CHAR(100)           NOT NULL,
    PRIMARY KEY ("categoryID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160201053352.744')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
