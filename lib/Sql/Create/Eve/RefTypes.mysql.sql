-- Sql/Create/Eve/RefTypes.sql
-- version 20161202044339.048
CREATE TABLE "{schema}"."{tablePrefix}eveRefTypes" (
    "refTypeID"   SMALLINT(5) UNSIGNED NOT NULL,
    "refTypeName" CHAR(100)            NOT NULL,
    PRIMARY KEY ("refTypeID")
);
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161202044339.048');
COMMIT;
