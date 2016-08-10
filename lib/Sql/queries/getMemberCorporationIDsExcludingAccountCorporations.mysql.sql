-- Sql/queries/getMemberCorporationIDsExcludingAccountCorporations.mysql.sql
-- version 20160810095509.555
-- noinspection SqlResolveForFile
-- @formatter:off
SELECT DISTINCT emc."corporationID"
 FROM "{schema}"."{tablePrefix}eveMemberCorporations" AS emc
 WHERE
 emc."corporationID" NOT IN (
 SELECT ac."corporationID"
 FROM "{schema}"."{tablePrefix}accountCharacters" AS ac
 WHERE
 emc."corporationID" = ac."corporationID");
