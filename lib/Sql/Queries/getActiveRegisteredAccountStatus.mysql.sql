-- Sql/Queries/getActiveRegisteredAccountStatus.mysql.sql
-- version 20161129034743.566
-- @formatter:off
SELECT yrk."keyID", yrk."vCode"
    FROM "{schema}"."{tablePrefix}yapealRegisteredKey" AS yrk
    JOIN "{schema}"."{tablePrefix}accountAPIKeyInfo" AS aaki ON (yrk."keyID" = aaki."keyID")
    WHERE aaki."type" IN ('Account', 'Character')
    AND yrk."active" = 1
    AND (yrk."activeAPIMask" & aaki."accessMask" & %1$s) <> 0;
