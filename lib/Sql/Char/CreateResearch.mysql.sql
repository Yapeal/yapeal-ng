-- Sql/Char/CreateResearch.sql
-- version 20160629053442.767
CREATE TABLE "{database}"."{table_prefix}charResearch" (
    "agentID"           BIGINT(20) UNSIGNED NOT NULL,
    "ownerID"           BIGINT(20) UNSIGNED NOT NULL,
    "pointsPerDay"      DOUBLE              NOT NULL,
    "remainderPoints"   DOUBLE              NOT NULL,
    "researchStartDate" DATETIME            NOT NULL,
    "skillTypeID"       BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID", "agentID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160629053442.767')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
