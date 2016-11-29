-- Sql/Create/Yapeal/RegisteredUploader.mysql.sql
-- version 20161129113301.081
CREATE TABLE "{schema}"."{tablePrefix}yapealRegisteredUploader" (
    "active"              TINYINT(1) UNSIGNED NOT NULL,
    "key"                 VARCHAR(255)        NOT NULL,
    "ownerID"             BIGINT(20) UNSIGNED NOT NULL,
    "uploadDestinationID" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID", "uploadDestinationID")
);
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161129113301.081');
COMMIT;
