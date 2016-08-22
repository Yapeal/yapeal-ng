-- Sql/Corp/CreateContactList.sql
-- version 20160629053416.744
CREATE TABLE "{schema}"."{tablePrefix}corpAllianceContactLabels" LIKE "{schema}"."{tablePrefix}charAllianceContactLabels";
CREATE TABLE "{schema}"."{tablePrefix}corpAllianceContactList" LIKE "{schema}"."{tablePrefix}charAllianceContactList";
CREATE TABLE "{schema}"."{tablePrefix}corpContactList" LIKE "{schema}"."{tablePrefix}charAllianceContactList";
CREATE TABLE "{schema}"."{tablePrefix}corpCorporateContactLabels" LIKE "{schema}"."{tablePrefix}charAllianceContactLabels";
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}utilDatabaseVersion" ("version")
    VALUES ('20160629053416.744')
    ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
