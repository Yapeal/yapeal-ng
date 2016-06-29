-- Sql/Util/CreateUploadDestination.sql
-- version 20160131212500.007
CREATE TABLE "{database}"."{table_prefix}utilXmlCache" (
    "accountKey"  INT(5) UNSIGNED NOT NULL DEFAULT 0,
    "apiName"     CHAR(32)        NOT NULL,
    "hash"        CHAR(40)        NOT NULL,
    "modified"    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    "sectionName" CHAR(8)         NOT NULL,
    "xml"         LONGTEXT,
    PRIMARY KEY ("hash")
);
ALTER TABLE "{database}"."{table_prefix}utilXmlCache"
    ADD INDEX "utilXmlCache1" ("sectionName");
ALTER TABLE "{database}"."{table_prefix}utilXmlCache"
    ADD INDEX "utilXmlCache2" ("apiName");
