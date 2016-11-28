-- Sql/queries/getCreateUtilDatabaseVersion.mysql.sql
-- version 20161127224340.210
CREATE TABLE "{schema}"."{tablePrefix}utilDatabaseVersion" (
    "version" CHAR(18) NOT NULL,
    PRIMARY KEY ("version")
);
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}utilDatabaseVersion" ("version")
    VALUES ('20161127224340.210');
COMMIT;
