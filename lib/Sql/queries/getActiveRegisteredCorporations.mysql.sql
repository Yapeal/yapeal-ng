-- Sql/queries/getActiveRegisteredCorporations.mysql.sql
-- version 20160810075105.998
-- noinspection SqlResolveForFile
-- @formatter:off
SELECT ac."corporationID", urk."keyID", urk."vCode"
 FROM "{schema}"."{tablePrefix}accountKeyBridge" AS akb
 JOIN "{schema}"."{tablePrefix}accountAPIKeyInfo" AS aaki ON (akb."keyID" = aaki."keyID")
 JOIN "{schema}"."{tablePrefix}utilRegisteredKey" AS urk ON (akb."keyID" = urk."keyID")
 JOIN "{schema}"."{tablePrefix}accountCharacters" AS ac ON (akb."characterID" = ac."characterID")
 WHERE
 aaki."type" = 'Corporation'
 AND urk."active" = 1
 AND (urk."activeAPIMask" & aaki."accessMask" & %1$s) <> 0
 AND aaki."expires" > now();
