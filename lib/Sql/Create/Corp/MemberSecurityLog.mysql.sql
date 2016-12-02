-- Sql/Create/Corp/MemberSecurityLog.sql
-- version 20161202044339.037
CREATE TABLE "{schema}"."{tablePrefix}corpMemberSecurityLog" (
    "changeTime"       DATETIME            NOT NULL,
    "characterID"      BIGINT(20) UNSIGNED NOT NULL,
    "issuerID"         BIGINT(20) UNSIGNED NOT NULL,
    "ownerID"          BIGINT(20) UNSIGNED NOT NULL,
    "roleLocationType" CHAR(100)           NOT NULL,
    PRIMARY KEY ("ownerID", "changeTime")
);
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161202044339.037');
COMMIT;
