-- Sql/Char/CreateCharacterSheet.sql
-- version 20160629013856.361
CREATE TABLE "{schema}"."{tablePrefix}charAttributes" (
    "charisma"     TINYINT(2) UNSIGNED NOT NULL,
    "intelligence" TINYINT(2) UNSIGNED NOT NULL,
    "memory"       TINYINT(2) UNSIGNED NOT NULL,
    "ownerID"      BIGINT(20) UNSIGNED NOT NULL,
    "perception"   TINYINT(2) UNSIGNED NOT NULL,
    "willpower"    TINYINT(2) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID")
);
CREATE TABLE "{schema}"."{tablePrefix}charCertificates" (
    "certificateID" BIGINT(20) UNSIGNED NOT NULL,
    "ownerID"       BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID", "certificateID")
);
CREATE TABLE "{schema}"."{tablePrefix}charCharacterSheet" (
    "allianceID"        BIGINT(20) UNSIGNED NOT NULL,
    "allianceName"      CHAR(100)           NOT NULL,
    "ancestry"          CHAR(24)            NOT NULL,
    "ancestryID"        BIGINT(20) UNSIGNED NOT NULL,
    "balance"           DECIMAL(17, 2)      NOT NULL,
    "bloodLine"         CHAR(24)            NOT NULL,
    "bloodLineID"       BIGINT(20) UNSIGNED NOT NULL,
    "characterID"       BIGINT(20) UNSIGNED NOT NULL,
    "cloneJumpDate"     DATETIME            NOT NULL DEFAULT '1970-01-01 00:00:01',
    "cloneName"         CHAR(100)           NOT NULL,
    "cloneSkillPoints"  BIGINT(20) UNSIGNED NOT NULL,
    "cloneTypeID"       BIGINT(20) UNSIGNED NOT NULL,
    "corporationID"     BIGINT(20) UNSIGNED NOT NULL,
    "corporationName"   CHAR(100)           NOT NULL,
    "DoB"               DATETIME            NOT NULL,
    "factionID"         BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
    "factionName"       CHAR(100)           NOT NULL DEFAULT '',
    "freeRespecs"       INT(4) UNSIGNED     NOT NULL DEFAULT 0,
    "freeSkillPoints"   BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
    "gender"            CHAR(6)             NOT NULL,
    "homeStationID"     BIGINT(20) UNSIGNED NOT NULL,
    "jumpActivation"    DATETIME            NOT NULL DEFAULT '1970-01-01 00:00:01',
    "jumpFatigue"       DATETIME            NOT NULL DEFAULT '1970-01-01 00:00:01',
    "jumpLastUpdate"    DATETIME            NOT NULL DEFAULT '1970-01-01 00:00:01',
    "lastRespecDate"    DATETIME            NOT NULL DEFAULT '1970-01-01 00:00:01',
    "lastTimedRespec"   DATETIME            NOT NULL DEFAULT '1970-01-01 00:00:01',
    "name"              CHAR(100)           NOT NULL,
    "ownerID"           BIGINT(20) UNSIGNED NOT NULL,
    "race"              CHAR(8)             NOT NULL,
    "remoteStationDate" DATETIME            NOT NULL DEFAULT '1970-01-01 00:00:01',
    PRIMARY KEY ("ownerID")
);
CREATE TABLE "{schema}"."{tablePrefix}charCorporationRoles" (
    "ownerID"  BIGINT(20) UNSIGNED NOT NULL,
    "roleID"   BIGINT(20) UNSIGNED NOT NULL,
    "roleName" CHAR(100)           NOT NULL,
    PRIMARY KEY ("ownerID", "roleID")
);
CREATE TABLE "{schema}"."{tablePrefix}charCorporationRolesAtBase" LIKE "{schema}"."{tablePrefix}charCorporationRoles";
CREATE TABLE "{schema}"."{tablePrefix}charCorporationRolesAtHQ" LIKE "{schema}"."{tablePrefix}charCorporationRoles";
CREATE TABLE "{schema}"."{tablePrefix}charCorporationRolesAtOther" LIKE "{schema}"."{tablePrefix}charCorporationRoles";
CREATE TABLE "{schema}"."{tablePrefix}charCorporationTitles" (
    "ownerID"   BIGINT(20) UNSIGNED NOT NULL,
    "titleID"   BIGINT(20) UNSIGNED NOT NULL,
    "titleName" CHAR(100)           NOT NULL,
    PRIMARY KEY ("ownerID", "titleID")
);
CREATE TABLE "{schema}"."{tablePrefix}charImplants" (
    "ownerID"  BIGINT(20) UNSIGNED NOT NULL,
    "typeID"   BIGINT(20) UNSIGNED NOT NULL,
    "typeName" CHAR(100)           NOT NULL,
    PRIMARY KEY ("ownerID", "typeID")
);
CREATE TABLE "{schema}"."{tablePrefix}charJumpCloneImplants" (
    "jumpCloneID" BIGINT(20) UNSIGNED NOT NULL,
    "ownerID"     BIGINT(20) UNSIGNED NOT NULL,
    "typeID"      BIGINT(20) UNSIGNED NOT NULL,
    "typeName"    CHAR(100)           NOT NULL,
    PRIMARY KEY ("ownerID", "jumpCloneID")
);
CREATE TABLE "{schema}"."{tablePrefix}charJumpClones" (
    "cloneName"   CHAR(100)           NOT NULL,
    "jumpCloneID" BIGINT(20) UNSIGNED NOT NULL,
    "locationID"  BIGINT(20) UNSIGNED NOT NULL,
    "ownerID"     BIGINT(20) UNSIGNED NOT NULL,
    "typeID"      BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID", "jumpCloneID")
);
CREATE TABLE "{schema}"."{tablePrefix}charSkills" (
    "level"       TINYINT(1) UNSIGNED NOT NULL,
    "ownerID"     BIGINT(20) UNSIGNED NOT NULL,
    "published"   TINYINT(1) UNSIGNED NOT NULL,
    "skillpoints" BIGINT(20) UNSIGNED NOT NULL,
    "typeID"      BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID", "typeID")
);
START TRANSACTION;
INSERT INTO "{schema}"."{tablePrefix}utilDatabaseVersion" ("version")
VALUES ('20160629013856.361')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
