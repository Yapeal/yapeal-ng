-- Sql/queries/getActiveRegisteredCharacters.mysql.sql
-- version 20160810074302.901
-- @formatter:off
SELECT ac."characterID", urk."keyID", urk."vCode"
 FROM "{schema}"."{tablePrefix}accountKeyBridge" AS akb
 JOIN "{schema}"."{tablePrefix}accountAPIKeyInfo" AS aaki ON (akb."keyID" = aaki."keyID")
 JOIN "{schema}"."{tablePrefix}utilRegisteredKey" AS urk ON (akb."keyID" = urk."keyID")
 JOIN "{schema}"."{tablePrefix}accountCharacters" AS ac ON (akb."characterID" = ac."characterID")
 WHERE
 aaki."type" IN ('Account', 'Character')
 AND urk."active" = 1
 AND (urk."activeAPIMask" & aaki."accessMask" & %1$s) <> 0
 AND aaki."expires" > now();
