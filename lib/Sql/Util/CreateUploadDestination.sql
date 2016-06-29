-- Sql/Util/CreateUploadDestination.sql
-- version 20160131212500.006
CREATE TABLE "{database}"."{table_prefix}utilUploadDestination" (
    "active"              TINYINT(1)   DEFAULT NULL,
    "name"                VARCHAR(25)  DEFAULT NULL,
    "uploadDestinationID" BIGINT(20) UNSIGNED NOT NULL,
    "url"                 VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY ("uploadDestinationID")
);
