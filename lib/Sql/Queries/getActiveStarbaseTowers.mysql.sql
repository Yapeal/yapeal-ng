-- Sql/Queries/getActiveStarbaseTowers.mysql.sql
-- version 20161129040318.943
-- @formatter:off
SELECT sl."itemID", ac."corporationID", yrk."keyID", yrk."vCode"
    FROM "{schema}"."{tablePrefix}accountKeyBridge" AS akb
    JOIN "{schema}"."{tablePrefix}accountAPIKeyInfo" AS aaki ON (akb."keyID" = aaki."keyID")
    JOIN "{schema}"."{tablePrefix}yapealRegisteredKey" AS yrk ON (akb."keyID" = yrk."keyID")
    JOIN "{schema}"."{tablePrefix}accountCharacters" AS ac ON (akb."characterID" = ac."characterID")
    JOIN "{schema}"."{tablePrefix}corpStarbaseList" AS sl ON (ac."corporationID" = sl."ownerID")
    WHERE aaki."type" = 'Corporation'
    AND yrk."active" = 1
    AND sl."ownerID" = % 2$s
    AND (yrk."activeAPIMask" & aaki."accessMask" & % 1$s) <> 0;
