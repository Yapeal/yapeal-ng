-- Sql/Util/CreateDatabaseVersion.sql
-- version 20160131212501.000
CREATE TABLE "{database}"."{table_prefix}utilDatabaseVersion" (
    "version" CHAR(18) NOT NULL,
    PRIMARY KEY ("version")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160131212501.000');
COMMIT;
