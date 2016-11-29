-- Sql/queries/getCreateYapealSchemaVersion.mysql.sql
-- version 20161129003801.886
CREATE TABLE "{schema}"."{tablePrefix}yapealSchemaVersion" (
    "version" CHAR(18) NOT NULL,
    PRIMARY KEY ("version")
);
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161129003801.886');
COMMIT;
