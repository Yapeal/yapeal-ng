-- Sql/Queries/getCreateYapealSchemaVersion.mysql.sql
-- version 20161129113301.000
CREATE TABLE "{schema}"."{tablePrefix}yapealSchemaVersion" (
    "version" CHAR(18) NOT NULL,
    PRIMARY KEY ("version")
);
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161129113301.000');
COMMIT;
