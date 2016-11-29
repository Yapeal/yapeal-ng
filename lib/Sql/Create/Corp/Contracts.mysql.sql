-- Sql/Create/Corp/Contracts.sql
-- version 20161129113301.054
CREATE TABLE "{schema}"."{tablePrefix}corpContracts" LIKE "{schema}"."{tablePrefix}charContracts";
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161129113301.054');
COMMIT;
