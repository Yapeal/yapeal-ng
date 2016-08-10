-- Sql/queries/getActiveRegisteredAccountStatus.nysql.sql
-- version 20160810093708.526
-- noinspection SqlResolveForFile
-- @formatter:off
SELECT urk."keyID",urk."vCode"
 FROM "{schema}"."{tablePrefix}utilRegisteredKey" AS urk
 JOIN "{schema}"."{tablePrefix}accountAPIKeyInfo" AS aaki
 ON (urk."keyID" = aaki."keyID")
 WHERE
 aaki."type" IN ('Account','Character')
 AND urk."active"=1
 AND (urk."activeAPIMask" & aaki."accessMask" & %1$s) <> 0;
