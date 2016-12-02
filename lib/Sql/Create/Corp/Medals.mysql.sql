-- Sql/Create/Corp/Medals.sql
-- version 20161202044339.035
CREATE TABLE "{schema}"."{tablePrefix}corpMedals" (
    "created"     DATETIME            NOT NULL,
    "creatorID"   BIGINT(20) UNSIGNED NOT NULL,
    "description" TEXT                NOT NULL,
    "medalID"     BIGINT(20) UNSIGNED NOT NULL,
    "ownerID"     BIGINT(20) UNSIGNED NOT NULL,
    "title"       CHAR(255) DEFAULT '',
    PRIMARY KEY ("ownerID", "medalID")
);
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161202044339.035');
COMMIT;
