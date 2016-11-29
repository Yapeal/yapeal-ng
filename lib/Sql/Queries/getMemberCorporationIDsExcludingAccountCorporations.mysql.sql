-- Sql/Queries/getMemberCorporationIDsExcludingAccountCorporations.mysql.sql
-- version 20161129113301.023
-- @formatter:off
SELECT DISTINCT emc."corporationID"
 FROM "{schema}"."{tablePrefix}eveMemberCorporations" AS emc
 WHERE
 emc."corporationID" NOT IN (
 SELECT ac."corporationID"
 FROM "{schema}"."{tablePrefix}accountCharacters" AS ac
 WHERE
 emc."corporationID" = ac."corporationID");
