-- Sql/Corp/CreateMemberMedals.sql
-- version 20160629053439.567
CREATE TABLE "{schema}"."{tablePrefix}corpMemberMedals" (
    "characterID" BIGINT(20) UNSIGNED NOT NULL,
    "issued"      DATETIME            NOT NULL,
    "issuerID"    BIGINT(20) UNSIGNED NOT NULL,
    "medalID"     BIGINT(20) UNSIGNED NOT NULL,
    "ownerID"     BIGINT(20) UNSIGNED NOT NULL,
    "reason"      TEXT,
    "status"      CHAR(8)             NOT NULL,
    PRIMARY KEY ("ownerID", "medalID", "characterID")
);
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20160629053439.567');
COMMIT;
