-- Sql/Queries/getActiveRegisteredCharacters.mysql.sql
-- version 20161202044339.064
-- @formatter:off
SELECT ac."characterID", yrk."keyID", yrk."vCode"
    FROM "{schema}"."{tablePrefix}accountKeyBridge" AS akb
    JOIN "{schema}"."{tablePrefix}accountAPIKeyInfo" AS aaki ON (akb."keyID" = aaki."keyID")
    JOIN "{schema}"."{tablePrefix}yapealRegisteredKey" AS yrk ON (akb."keyID" = yrk."keyID")
    JOIN "{schema}"."{tablePrefix}accountCharacters" AS ac ON (akb."characterID" = ac."characterID")
    WHERE aaki."type" IN ('Account', 'Character')
    AND yrk."active" = 1
    AND (yrk."activeAPIMask" & aaki."accessMask" & %1$s) <> 0
    AND aaki."expires" > now();
