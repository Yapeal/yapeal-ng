-- Sql/Char/CreateResearch.sql
-- version 20160201053948.735
CREATE TABLE "{database}"."{table_prefix}charResearch" (
    "agentID" BIGINT(20) UNSIGNED NOT NULL,
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "pointsPerDay" VARCHAR(255) DEFAULT '',
    "remainderPoints" VARCHAR(255) DEFAULT '',
    "researchStartDate" DATETIME NOT NULL DEFAULT '1970-01-01 00:00:01',
    "skillTypeID" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID","agentID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
('20160201053948.735')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
