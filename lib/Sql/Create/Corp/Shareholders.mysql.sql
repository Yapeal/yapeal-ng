-- Sql/Corp/CreateShareholders.sql
-- version 20160629053443.777
CREATE TABLE "{schema}"."{tablePrefix}corpCorporations" (
    "ownerID"         BIGINT(20) UNSIGNED NOT NULL,
    "shareholderID"   BIGINT(20) UNSIGNED NOT NULL,
    "shareholderName" CHAR(100)           NOT NULL,
    "shares"          BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID", "shareholderID")
);
CREATE TABLE "{schema}"."{tablePrefix}corpShareholders" (
    "ownerID"                    BIGINT(20) UNSIGNED NOT NULL,
    "shareholderCorporationID"   BIGINT(20) UNSIGNED NOT NULL,
    "shareholderCorporationName" CHAR(100)           NOT NULL,
    "shareholderID"              BIGINT(20) UNSIGNED NOT NULL,
    "shareholderName"            CHAR(100)           NOT NULL,
    "shares"                     BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID", "shareholderID")
);
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20160629053443.777');
COMMIT;
