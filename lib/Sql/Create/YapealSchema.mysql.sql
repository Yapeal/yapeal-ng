-- Sql/Create/YapealSchema.mysql.sql
-- version 20161202044339.000
CREATE SCHEMA "{schema}"
    DEFAULT CHARACTER SET '{characterSet}'
    DEFAULT COLLATE '{characterCollate}';
CREATE TABLE "{schema}"."{tablePrefix}yapealSchemaVersion" (
    "version" CHAR(18) NOT NULL,
    PRIMARY KEY ("version")
);
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161202044339.000');
COMMIT;
