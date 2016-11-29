-- Sql/Corp/CreateStarbaseDetail.mysql.sql
-- version 20160822061939.133
CREATE TABLE "{database}"."{tablePrefix}corpCombatSettings" (
    "itemID"                  BIGINT(20) UNSIGNED    NOT NULL,
    "onAggressionEnabled"     TINYINT(1)             NOT NULL,
    "onCorporationWarEnabled" TINYINT(1)             NOT NULL,
    "onStandingDropStanding"  DECIMAL(5, 2) UNSIGNED NOT NULL,
    "onStatusDropEnabled"     TINYINT(1)             NOT NULL,
    "onStatusDropStanding"    DECIMAL(5, 2) UNSIGNED NOT NULL,
    "ownerID"                 BIGINT(20) UNSIGNED    NOT NULL,
    "useStandingsFromOwnerID" BIGINT(20) UNSIGNED    NOT NULL,
    PRIMARY KEY ("ownerID", "itemID")
);
CREATE TABLE "{database}"."{tablePrefix}corpFuel" (
    "itemID"   BIGINT(20) UNSIGNED NOT NULL,
    "ownerID"  BIGINT(20) UNSIGNED NOT NULL,
    "quantity" BIGINT(20) UNSIGNED NOT NULL,
    "typeID"   BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID", "itemID")
);
CREATE TABLE "{database}"."{table_prefix}corpGeneralSettings" (
    "allowAllianceMembers"    TINYINT(1)           NOT NULL,
    "allowCorporationMembers" TINYINT(1)           NOT NULL,
    "deployFlags"             SMALLINT(5) UNSIGNED NOT NULL,
    "itemID"                  BIGINT(20) UNSIGNED  NOT NULL,
    "ownerID"                 BIGINT(20) UNSIGNED  NOT NULL,
    "usageFlags"              SMALLINT(5) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID", "itemID")
);
CREATE TABLE "{database}"."{tablePrefix}corpStarbaseDetail" (
    "itemID"          BIGINT(20) UNSIGNED NOT NULL,
    "onlineTimestamp" DATETIME            NOT NULL,
    "ownerID"         BIGINT(20) UNSIGNED NOT NULL,
    "state"           TINYINT(2) UNSIGNED NOT NULL,
    "stateTimestamp"  DATETIME            NOT NULL,
    PRIMARY KEY ("ownerID", "itemID")
);
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}utilDatabaseVersion" ("version")
    VALUES ('20160822061939.133')
    ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
