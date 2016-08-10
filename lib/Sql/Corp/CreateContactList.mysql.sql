-- Sql/Corp/CreateContactList.sql
-- version 20160629053416.744
CREATE TABLE "{schema}"."{table_prefix}corpAllianceContactLabels" LIKE "{schema}"."{table_prefix}charAllianceContactLabels";
CREATE TABLE "{schema}"."{table_prefix}corpAllianceContactList" LIKE "{schema}"."{table_prefix}charAllianceContactList";
CREATE TABLE "{schema}"."{table_prefix}corpContactList" LIKE "{schema}"."{table_prefix}charAllianceContactList";
CREATE TABLE "{schema}"."{table_prefix}corpCorporateContactLabels" LIKE "{schema}"."{table_prefix}charAllianceContactLabels";
START TRANSACTION;
INSERT INTO "{schema}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160629053416.744')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
