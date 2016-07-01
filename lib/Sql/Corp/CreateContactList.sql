-- Sql/Corp/CreateContactList.sql
-- version 20160629053416.744
CREATE TABLE "{database}"."{table_prefix}corpAllianceContactLabels" LIKE "{database}"."{table_prefix}charAllianceContactLabels";
CREATE TABLE "{database}"."{table_prefix}corpAllianceContactList" LIKE "{database}"."{table_prefix}charAllianceContactList";
CREATE TABLE "{database}"."{table_prefix}corpContactList" LIKE "{database}"."{table_prefix}charAllianceContactList";
CREATE TABLE "{database}"."{table_prefix}corpCorporateContactLabels" LIKE "{database}"."{table_prefix}charAllianceContactLabels";
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160629053416.744')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
