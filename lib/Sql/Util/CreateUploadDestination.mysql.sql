-- Sql/Util/CreateUploadDestination.sql
-- version 20160131212500.006
CREATE TABLE "{schema}"."{tablePrefix}utilUploadDestination" (
    "active"              TINYINT(1) UNSIGNED NOT NULL,
    "name"                CHAR(50)            NOT NULL,
    "uploadDestinationID" BIGINT(20) UNSIGNED NOT NULL,
    "url"                 CHAR(255)           NOT NULL,
    PRIMARY KEY ("uploadDestinationID")
);
