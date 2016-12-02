-- Sql/Create/Char/Blueprints.sql
-- version 20161202044339.007
CREATE TABLE "{schema}"."{tablePrefix}charBlueprints" (
    "flagID"             BIGINT(20) UNSIGNED NOT NULL,
    "itemID"             BIGINT(20) UNSIGNED NOT NULL,
    "locationID"         BIGINT(20) UNSIGNED NOT NULL,
    "materialEfficiency" TINYINT(3)          NOT NULL,
    "ownerID"            BIGINT(20) UNSIGNED NOT NULL,
    "quantity"           BIGINT(20)          NOT NULL,
    "runs"               BIGINT(20)          NOT NULL,
    "timeEfficiency"     TINYINT(3)          NOT NULL,
    "typeID"             BIGINT(20) UNSIGNED NOT NULL,
    "typeName"           CHAR(100)           NOT NULL,
    PRIMARY KEY ("ownerID", "itemID")
);
-- Used altered index name(s) since they get copied to corp table during create ... like ...
ALTER TABLE "{schema}"."{tablePrefix}charBlueprints"
    ADD INDEX "Blueprints1" ("ownerID", "locationID");
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161202044339.007');
COMMIT;
