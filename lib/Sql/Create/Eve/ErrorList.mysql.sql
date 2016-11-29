-- Sql/Create/Eve/ErrorList.sql
-- version 20161129113301.072
CREATE TABLE "{schema}"."{tablePrefix}eveErrorList" (
    "errorCode" SMALLINT(4) UNSIGNED NOT NULL,
    "errorText" TEXT,
    PRIMARY KEY ("errorCode")
);
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161129113301.072');
COMMIT;
