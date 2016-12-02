-- Sql/Create/Corp/ContactList.sql
-- version 20161202044339.027
CREATE TABLE "{schema}"."{tablePrefix}corpAllianceContactLabels" LIKE "{schema}"."{tablePrefix}charAllianceContactLabels";
CREATE TABLE "{schema}"."{tablePrefix}corpAllianceContactList" LIKE "{schema}"."{tablePrefix}charAllianceContactList";
CREATE TABLE "{schema}"."{tablePrefix}corpContactList" LIKE "{schema}"."{tablePrefix}charAllianceContactList";
CREATE TABLE "{schema}"."{tablePrefix}corpCorporateContactLabels" LIKE "{schema}"."{tablePrefix}charAllianceContactLabels";
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161202044339.027');
COMMIT;
