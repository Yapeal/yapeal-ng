-- Sql/Create/Eve/ErrorList.sql
-- version 20161202044339.047
CREATE TABLE "{schema}"."{tablePrefix}eveErrorList" (
    "errorCode" SMALLINT(4) UNSIGNED NOT NULL,
    "errorText" TEXT,
    PRIMARY KEY ("errorCode")
);
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161202044339.047');
COMMIT;
