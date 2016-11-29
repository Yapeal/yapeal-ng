-- Sql/Create/Eve/RefTypes.sql
-- version 20161129113301.073
CREATE TABLE "{schema}"."{tablePrefix}eveRefTypes" (
    "refTypeID"   SMALLINT(5) UNSIGNED NOT NULL,
    "refTypeName" CHAR(100)            NOT NULL,
    PRIMARY KEY ("refTypeID")
);
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161129113301.073');
COMMIT;
