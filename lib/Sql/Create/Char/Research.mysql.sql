-- Sql/Create/Char/Research.sql
-- version 20161202044339.018
CREATE TABLE "{schema}"."{tablePrefix}charResearch" (
    "agentID"           BIGINT(20) UNSIGNED NOT NULL,
    "ownerID"           BIGINT(20) UNSIGNED NOT NULL,
    "pointsPerDay"      DOUBLE              NOT NULL,
    "remainderPoints"   DOUBLE              NOT NULL,
    "researchStartDate" DATETIME            NOT NULL,
    "skillTypeID"       BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID", "agentID")
);
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161202044339.018');
COMMIT;
