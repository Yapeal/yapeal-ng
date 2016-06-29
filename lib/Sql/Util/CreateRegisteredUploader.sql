-- Sql/Util/CreateRegisteredUploader.sql
-- version 20160131212500.005
CREATE TABLE "{database}"."{table_prefix}utilRegisteredUploader" (
    "active"              TINYINT(1)   DEFAULT NULL,
    "key"                 VARCHAR(255) DEFAULT NULL,
    "ownerID"             BIGINT(20) UNSIGNED NOT NULL,
    "uploadDestinationID" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID", "uploadDestinationID")
);
