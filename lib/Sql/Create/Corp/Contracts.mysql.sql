-- Sql/Create/Corp/Contracts.sql
-- version 20161202044339.029
CREATE TABLE "{schema}"."{tablePrefix}corpContracts" LIKE "{schema}"."{tablePrefix}charContracts";
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161202044339.029');
COMMIT;
