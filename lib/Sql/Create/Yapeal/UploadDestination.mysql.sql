-- Sql/Create/Yapeal/UploadDestination.mysql.sql
-- version 20161129113301.082
CREATE TABLE "{schema}"."{tablePrefix}yapealUploadDestination" (
    "active"              TINYINT(1) UNSIGNED NOT NULL,
    "name"                CHAR(50)            NOT NULL,
    "uploadDestinationID" BIGINT(20) UNSIGNED NOT NULL,
    "url"                 CHAR(255)           NOT NULL,
    PRIMARY KEY ("uploadDestinationID")
);
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161129113301.082');
COMMIT;
