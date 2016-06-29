-- Sql/Char/CreateCharacterSheet.sql
-- version 20160629013856.361
CREATE TABLE "{database}"."{table_prefix}charCertificates" (
    "certificateID" BIGINT(20) UNSIGNED NOT NULL,
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID","certificateID")
);
CREATE TABLE "{database}"."{table_prefix}charCharacterSheet" (
    "allianceID" BIGINT(20) UNSIGNED NOT NULL,
    "allianceName" CHAR(100) NOT NULL,
    "ancestry" TEXT NOT NULL,
    "ancestryID" BIGINT(20) UNSIGNED NOT NULL,
    "balance" DECIMAL(17, 2) NOT NULL,
    "bloodLine" TEXT NOT NULL,
    "bloodLineID" BIGINT(20) UNSIGNED NOT NULL,
    "characterID" BIGINT(20) UNSIGNED NOT NULL,
    "cloneJumpDate" DATETIME NOT NULL DEFAULT '1970-01-01 00:00:01',
    "cloneName" CHAR(100) NOT NULL,
    "cloneSkillPoints" TEXT NOT NULL,
    "cloneTypeID" BIGINT(20) UNSIGNED NOT NULL,
    "corporationID" BIGINT(20) UNSIGNED NOT NULL,
    "corporationName" CHAR(100) NOT NULL,
    "DoB" TEXT NOT NULL,
    "factionID" BIGINT(20) UNSIGNED NOT NULL,
    "factionName" CHAR(100) NOT NULL,
    "freeRespecs" TEXT NOT NULL,
    "freeSkillPoints" TEXT NOT NULL,
    "gender" TEXT NOT NULL,
    "homeStationID" BIGINT(20) UNSIGNED NOT NULL,
    "jumpActivation" TEXT NOT NULL,
    "jumpFatigue" TEXT NOT NULL,
    "jumpLastUpdate" DATETIME NOT NULL DEFAULT '1970-01-01 00:00:01',
    "lastRespecDate" DATETIME NOT NULL DEFAULT '1970-01-01 00:00:01',
    "lastTimedRespec" DATETIME NOT NULL DEFAULT '1970-01-01 00:00:01',
    "name" CHAR(100) NOT NULL,
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "race" TEXT NOT NULL,
    "remoteStationDate" DATETIME NOT NULL DEFAULT '1970-01-01 00:00:01',
    PRIMARY KEY ("ownerID")
);
CREATE TABLE "{database}"."{table_prefix}charCorporationRoles" (
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "roleID" BIGINT(20) UNSIGNED NOT NULL,
    "roleName" CHAR(100) NOT NULL,
    PRIMARY KEY ("ownerID","roleID")
);
CREATE TABLE "{database}"."{table_prefix}charCorporationRolesAtBase" (
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "roleID" BIGINT(20) UNSIGNED NOT NULL,
    "roleName" CHAR(100) NOT NULL,
    PRIMARY KEY ("ownerID","roleID")
);
CREATE TABLE "{database}"."{table_prefix}charCorporationRolesAtHQ" (
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "roleID" BIGINT(20) UNSIGNED NOT NULL,
    "roleName" CHAR(100) NOT NULL,
    PRIMARY KEY ("ownerID","roleID")
);
CREATE TABLE "{database}"."{table_prefix}charCorporationRolesAtOther" (
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "roleID" BIGINT(20) UNSIGNED NOT NULL,
    "roleName" CHAR(100) NOT NULL,
    PRIMARY KEY ("ownerID","roleID")
);
CREATE TABLE "{database}"."{table_prefix}charCorporationTitles" (
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "titleID" BIGINT(20) UNSIGNED NOT NULL,
    "titleName" CHAR(100) NOT NULL,
    PRIMARY KEY ("ownerID","titleID")
);
CREATE TABLE "{database}"."{table_prefix}charImplants" (
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "typeID" BIGINT(20) UNSIGNED NOT NULL,
    "typeName" CHAR(100) NOT NULL,
    PRIMARY KEY ("ownerID","typeID")
);
CREATE TABLE "{database}"."{table_prefix}charJumpCloneImplants" (
    "jumpCloneID" BIGINT(20) UNSIGNED NOT NULL,
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "typeID" BIGINT(20) UNSIGNED NOT NULL,
    "typeName" CHAR(100) NOT NULL,
    PRIMARY KEY ("ownerID","jumpCloneID")
);
CREATE TABLE "{database}"."{table_prefix}charJumpClones" (
    "cloneName" CHAR(100) NOT NULL,
    "jumpCloneID" BIGINT(20) UNSIGNED NOT NULL,
    "locationID" BIGINT(20) UNSIGNED NOT NULL,
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "typeID" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID","jumpCloneID")
);
CREATE TABLE "{database}"."{table_prefix}charSkills" (
    "level" SMALLINT(4) UNSIGNED NOT NULL,
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "published" VARCHAR(255) DEFAULT '',
    "skillpoints" VARCHAR(255) DEFAULT '',
    "typeID" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID","typeID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
('20160629013856.361')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
