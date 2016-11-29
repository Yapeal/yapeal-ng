-- Sql/Create/Corp/CorporationSheet.mysql.sql
-- version 20161129113301.055
CREATE TABLE "{database}"."{tablePrefix}corpCorporationSheet" (
    "allianceID"      BIGINT(20) UNSIGNED    NOT NULL,
    "allianceName"    CHAR(100)              NOT NULL,
    "ceoID"           BIGINT(20) UNSIGNED    NOT NULL,
    "ceoName"         CHAR(100)              NOT NULL,
    "corporationID"   BIGINT(20) UNSIGNED    NOT NULL,
    "corporationName" CHAR(100)              NOT NULL,
    "description"     TEXT,
    "factionID"       BIGINT(20) UNSIGNED    NOT NULL DEFAULT 0,
    "factionName"     CHAR(100)              NOT NULL DEFAULT '',
    "memberCount"     BIGINT(20) UNSIGNED    NOT NULL,
    "memberLimit"     BIGINT(20) UNSIGNED    NOT NULL DEFAULT 0,
    "shares"          BIGINT(20) UNSIGNED    NOT NULL,
    "stationID"       BIGINT(20) UNSIGNED    NOT NULL,
    "stationName"     CHAR(100)              NOT NULL,
    "taxRate"         DECIMAL(5, 2) UNSIGNED NOT NULL,
    "ticker"          CHAR(5)                NOT NULL,
    "url"             CHAR(255)              NOT NULL DEFAULT '',
    PRIMARY KEY ("corporationID")
);
CREATE TABLE "{database}"."{tablePrefix}corpDivisions" (
    "ownerID"     BIGINT(20) UNSIGNED  NOT NULL,
    "accountKey"  SMALLINT(5) UNSIGNED NOT NULL,
    "description" CHAR(255)            NOT NULL,
    PRIMARY KEY ("ownerID", "accountKey")
);
CREATE TABLE "{database}"."{tablePrefix}corpLogo" (
    "ownerID"   BIGINT(20) UNSIGNED  NOT NULL,
    "color1"    SMALLINT(5) UNSIGNED NOT NULL,
    "color2"    SMALLINT(5) UNSIGNED NOT NULL,
    "color3"    SMALLINT(5) UNSIGNED NOT NULL,
    "graphicID" BIGINT(20) UNSIGNED  NOT NULL,
    "shape1"    SMALLINT(5) UNSIGNED NOT NULL,
    "shape2"    SMALLINT(5) UNSIGNED NOT NULL,
    "shape3"    SMALLINT(5) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID", "graphicID")
);
CREATE TABLE "{database}"."{tablePrefix}corpWalletDivisions" (
    "ownerID"     BIGINT(20) UNSIGNED  NOT NULL,
    "accountKey"  SMALLINT(4) UNSIGNED NOT NULL,
    "description" CHAR(255)            NOT NULL,
    PRIMARY KEY ("ownerID", "accountKey")
);
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161129113301.055');
COMMIT;
