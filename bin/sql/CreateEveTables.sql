SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
DROP TABLE IF EXISTS `{database}`.`{table_prefix}eveAllianceList`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}eveAllianceList` (
    `allianceID`     BIGINT(20) UNSIGNED NOT NULL,
    `executorCorpID` BIGINT(20) UNSIGNED DEFAULT NULL,
    `memberCount`    BIGINT(20) UNSIGNED DEFAULT NULL,
    `name`      CHAR(50) DEFAULT NULL,
    `shortName` CHAR(5) DEFAULT NULL,
    `startDate`      DATETIME DEFAULT NULL,
    PRIMARY KEY (`allianceID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}eveCharactersKillsLastWeek`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}eveCharactersKillsLastWeek` (
    `characterID`   BIGINT(20) UNSIGNED NOT NULL,
    `characterName` CHAR(24) DEFAULT NULL,
    `kills`         BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY (`characterID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}eveCharactersKillsTotal`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}eveCharactersKillsTotal` (
    `characterID`   BIGINT(20) UNSIGNED NOT NULL,
    `characterName` CHAR(24) DEFAULT NULL,
    `kills`         BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY (`characterID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}eveCharactersKillsYesterday`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}eveCharactersKillsYesterday` (
    `characterID`   BIGINT(20) UNSIGNED NOT NULL,
    `characterName` CHAR(24) DEFAULT NULL,
    `kills`         BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY (`characterID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}eveCharactersVictoryPointsLastWeek`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}eveCharactersVictoryPointsLastWeek` (
    `characterID`   BIGINT(20) UNSIGNED NOT NULL,
    `characterName` CHAR(24) DEFAULT NULL,
    `victoryPoints` BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY (`characterID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}eveCharactersVictoryPointsTotal`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}eveCharactersVictoryPointsTotal` (
    `characterID`   BIGINT(20) UNSIGNED NOT NULL,
    `characterName` CHAR(24) DEFAULT NULL,
    `victoryPoints` BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY (`characterID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}eveCharactersVictoryPointsYesterday`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}eveCharactersVictoryPointsYesterday` (
    `characterID`   BIGINT(20) UNSIGNED NOT NULL,
    `characterName` CHAR(24) DEFAULT NULL,
    `victoryPoints` BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY (`characterID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}eveConquerableStationList`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}eveConquerableStationList` (
    `corporationID`   BIGINT(20) UNSIGNED DEFAULT NULL,
    `corporationName` CHAR(50) DEFAULT NULL,
    `solarSystemID`   BIGINT(20) UNSIGNED DEFAULT NULL,
    `stationID`       BIGINT(20) UNSIGNED NOT NULL,
    `stationName`     CHAR(50) DEFAULT NULL,
    `stationTypeID`   BIGINT(20) UNSIGNED DEFAULT NULL,
    PRIMARY KEY (`stationID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}eveCorporationsKillsLastWeek`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}eveCorporationsKillsLastWeek` (
    `corporationID`   BIGINT(20) UNSIGNED NOT NULL,
    `corporationName` CHAR(50) DEFAULT NULL,
    `kills`           BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY (`corporationID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}eveCorporationsKillsTotal`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}eveCorporationsKillsTotal` (
    `corporationID`   BIGINT(20) UNSIGNED NOT NULL,
    `corporationName` CHAR(50) DEFAULT NULL,
    `kills`           BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY (`corporationID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}eveCorporationsKillsYesterday`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}eveCorporationsKillsYesterday` (
    `corporationID`   BIGINT(20) UNSIGNED NOT NULL,
    `corporationName` CHAR(50) DEFAULT NULL,
    `kills`           BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY (`corporationID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}eveCorporationsVictoryPointsLastWeek`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}eveCorporationsVictoryPointsLastWeek` (
    `corporationID`   BIGINT(20) UNSIGNED NOT NULL,
    `corporationName` CHAR(50) DEFAULT NULL,
    `victoryPoints`   BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY (`corporationID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}eveCorporationsVictoryPointsTotal`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}eveCorporationsVictoryPointsTotal` (
    `corporationID`   BIGINT(20) UNSIGNED NOT NULL,
    `corporationName` CHAR(50) DEFAULT NULL,
    `victoryPoints`   BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY (`corporationID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}eveCorporationsVictoryPointsYesterday`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}eveCorporationsVictoryPointsYesterday` (
    `corporationID`   BIGINT(20) UNSIGNED NOT NULL,
    `corporationName` CHAR(50) DEFAULT NULL,
    `victoryPoints`   BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY (`corporationID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}eveErrorList`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}eveErrorList` (
    `errorCode` SMALLINT(3) UNSIGNED NOT NULL,
    `errorText` TEXT,
    PRIMARY KEY (`errorCode`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}eveFactions`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}eveFactions` (
    `factionID`              BIGINT(20) UNSIGNED NOT NULL,
    `factionName` CHAR(24) DEFAULT NULL,
    `killsYesterday`         BIGINT(20) UNSIGNED NOT NULL,
    `killsLastWeek`          BIGINT(20) UNSIGNED NOT NULL,
    `killsTotal`             BIGINT(20) UNSIGNED NOT NULL,
    `pilots`                 BIGINT(20) UNSIGNED NOT NULL,
    `systemsControlled`      BIGINT(20) UNSIGNED NOT NULL,
    `victoryPointsYesterday` BIGINT(20) UNSIGNED NOT NULL,
    `victoryPointsLastWeek`  BIGINT(20) UNSIGNED NOT NULL,
    `victoryPointsTotal`     BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY (`factionID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}eveFactionsKillsLastWeek`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}eveFactionsKillsLastWeek` (
    `factionID`   BIGINT(20) UNSIGNED NOT NULL,
    `factionName` CHAR(24) DEFAULT NULL,
    `kills`       BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY (`factionID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}eveFactionsKillsTotal`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}eveFactionsKillsTotal` (
    `factionID`   BIGINT(20) UNSIGNED NOT NULL,
    `factionName` CHAR(24) DEFAULT NULL,
    `kills`       BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY (`factionID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}eveFactionsKillsYesterday`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}eveFactionsKillsYesterday` (
    `factionID`   BIGINT(20) UNSIGNED NOT NULL,
    `factionName` CHAR(24) DEFAULT NULL,
    `kills`       BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY (`factionID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}eveFactionsVictoryPointsLastWeek`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}eveFactionsVictoryPointsLastWeek` (
    `factionID`     BIGINT(20) UNSIGNED NOT NULL,
    `factionName` CHAR(24) DEFAULT NULL,
    `victoryPoints` BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY (`factionID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}eveFactionsVictoryPointsTotal`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}eveFactionsVictoryPointsTotal` (
    `factionID`     BIGINT(20) UNSIGNED NOT NULL,
    `factionName` CHAR(24) DEFAULT NULL,
    `victoryPoints` BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY (`factionID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}eveFactionsVictoryPointsYesterday`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}eveFactionsVictoryPointsYesterday` (
    `factionID`     BIGINT(20) UNSIGNED NOT NULL,
    `factionName` CHAR(24) DEFAULT NULL,
    `victoryPoints` BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY (`factionID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}eveFactionWars`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}eveFactionWars` (
    `factionID`   BIGINT(20) UNSIGNED NOT NULL,
    `factionName` CHAR(24) DEFAULT NULL,
    `againstID`   BIGINT(20) UNSIGNED NOT NULL,
    `againstName` CHAR(24) DEFAULT NULL
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}eveFacWarStats`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}eveFacWarStats` (
    `killsYesterday`         BIGINT(20) UNSIGNED NOT NULL,
    `killsLastWeek`          BIGINT(20) UNSIGNED NOT NULL,
    `killsTotal`             BIGINT(20) UNSIGNED NOT NULL,
    `victoryPointsYesterday` BIGINT(20) UNSIGNED NOT NULL,
    `victoryPointsLastWeek`  BIGINT(20) UNSIGNED NOT NULL,
    `victoryPointsTotal`     BIGINT(20) UNSIGNED NOT NULL
)
    ENGINE =InnoDB;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}eveMemberCorporations`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}eveMemberCorporations` (
    `allianceID`    BIGINT(20) UNSIGNED NOT NULL,
    `corporationID` BIGINT(20) UNSIGNED NOT NULL,
    `startDate`     DATETIME DEFAULT NULL,
    PRIMARY KEY (`corporationID`)
)
    ENGINE =InnoDB;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}eveRefTypes`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}eveRefTypes` (
    `refTypeID`   SMALLINT(5) UNSIGNED NOT NULL,
    `refTypeName` VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (`refTypeID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}eveTypeName`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}eveTypeName` (
    `typeID`   SMALLINT(5) UNSIGNED NOT NULL,
    `typeName` CHAR(50) DEFAULT NULL,
    PRIMARY KEY (`typeID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
