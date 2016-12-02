-- Sql/Create/Eve/CertificateTree.sql
-- version 20161202044339.045
CREATE TABLE "{schema}"."{tablePrefix}eveCertificateTree" (
    "categoryID"   BIGINT(20) UNSIGNED NOT NULL,
    "categoryName" CHAR(100)           NOT NULL,
    PRIMARY KEY ("categoryID")
);
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161202044339.045');
COMMIT;
