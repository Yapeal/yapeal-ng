-- Sql/Util/CreateRegisteredKey.sql
-- version 20160131212500.004
CREATE TABLE "{schema}"."{tablePrefix}utilRegisteredKey" (
    "active"        TINYINT(1) UNSIGNED NOT NULL,
    "activeAPIMask" BIGINT(20) UNSIGNED NOT NULL,
    "keyID"         BIGINT(20) UNSIGNED NOT NULL,
    "vCode"         CHAR(64)            NOT NULL,
    PRIMARY KEY ("keyID")
);
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}utilRegisteredKey" ("activeAPIMask", "active", "keyID", "vCode")
    VALUES (8388608, 1, 1156, 'abc123');
COMMIT;
