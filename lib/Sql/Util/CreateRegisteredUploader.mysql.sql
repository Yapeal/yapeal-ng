-- Sql/Util/CreateRegisteredUploader.sql
-- version 20160131212500.005
CREATE TABLE "{schema}"."{table_prefix}utilRegisteredUploader" (
    "active"              TINYINT(1) UNSIGNED NOT NULL,
    "key"                 VARCHAR(255)        NOT NULL,
    "ownerID"             BIGINT(20) UNSIGNED NOT NULL,
    "uploadDestinationID" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID", "uploadDestinationID")
);
