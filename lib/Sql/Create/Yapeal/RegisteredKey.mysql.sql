-- Sql/Create/Yapeal/RegisteredKey.mysql.sql
-- version 20161202044339.056
CREATE TABLE "{schema}"."{tablePrefix}yapealRegisteredKey" (
    "active"        TINYINT(1) UNSIGNED NOT NULL,
    "activeAPIMask" BIGINT(20) UNSIGNED NOT NULL,
    "keyID"         BIGINT(20) UNSIGNED NOT NULL,
    "vCode"         CHAR(64)            NOT NULL,
    PRIMARY KEY ("keyID")
);
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161202044339.056');
INSERT INTO "{schema}"."{tablePrefix}yapealRegisteredKey" ("activeAPIMask", "active", "keyID", "vCode")
    VALUES (8388608, 1, 1156, 'abc123');
COMMIT;
