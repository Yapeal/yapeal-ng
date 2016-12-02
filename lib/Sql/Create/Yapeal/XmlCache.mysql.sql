-- Sql/Create/Yapeal/XmlCache.mysql.sql
-- version 20161202044339.059
CREATE TABLE "{schema}"."{tablePrefix}yapealXmlCache" (
    "accountKey"  SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0,
    "apiName"     CHAR(32)             NOT NULL,
    "hash"        CHAR(64)             NOT NULL,
    "modified"    TIMESTAMP            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    "sectionName" CHAR(8)              NOT NULL,
    "xml"         LONGTEXT,
    PRIMARY KEY ("hash")
);
ALTER TABLE "{schema}"."{tablePrefix}yapealXmlCache"
    ADD INDEX "yapealXmlCache1" ("sectionName");
ALTER TABLE "{schema}"."{tablePrefix}yapealXmlCache"
    ADD INDEX "yapealXmlCache2" ("apiName");
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161202044339.059');
COMMIT;
