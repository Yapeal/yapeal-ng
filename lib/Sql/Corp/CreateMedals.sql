-- Sql/Corp/CreateMedals.sql
-- version 20160201053945.713
CREATE TABLE "{database}"."{table_prefix}corpMedals" (
    "created" VARCHAR(255) DEFAULT '',
    "creatorID" BIGINT(20) UNSIGNED NOT NULL,
    "description" TEXT NOT NULL,
    "medalID" BIGINT(20) UNSIGNED NOT NULL,
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "title" VARCHAR(255) DEFAULT '',
    PRIMARY KEY ("ownerID","medalID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
('20160201053945.713')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
