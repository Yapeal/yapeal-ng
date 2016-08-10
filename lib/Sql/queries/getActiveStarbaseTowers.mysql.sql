-- Sql/queries/getActiveStarbaseTowers.mysql.sql
-- version 20160810094104.325
-- noinspection SqlResolveForFile
-- @formatter:off
SELECT sl."itemID",ac."corporationID",urk."keyID",urk."vCode"
 FROM "{schema}"."{tablePrefix}accountKeyBridge" AS akb
 JOIN "{schema}"."{tablePrefix}accountAPIKeyInfo" AS aaki
 ON (akb."keyID" = aaki."keyID")
 JOIN "{schema}"."{tablePrefix}utilRegisteredKey" AS urk
 ON (akb."keyID" = urk."keyID")
 JOIN "{schema}"."{tablePrefix}accountCharacters" AS ac
 ON (akb."characterID" = ac."characterID")
 JOIN "{schema}"."{tablePrefix}corpStarbaseList" AS sl
 ON (ac."corporationID" = sl."ownerID")
 WHERE
 aaki."type" = 'Corporation'
 AND urk."active" = 1
 AND sl."ownerID" = %2$s
 AND (urk."activeAPIMask" & aaki."accessMask" & %1$s) <> 0
