-- Sql/Api/CreateCallList.sql
-- version 20160201053352.424
CREATE TABLE "{database}"."{table_prefix}apiCallList" (
    "description" TEXT NOT NULL,
    "groupID" BIGINT(20) UNSIGNED NOT NULL,
    "name" CHAR(100) NOT NULL,
    PRIMARY KEY ("groupID")
);
CREATE TABLE "{database}"."{table_prefix}apiCalls" (
    "accessMask" BIGINT(20) UNSIGNED NOT NULL,
    "description" TEXT NOT NULL,
    "groupID" BIGINT(20) UNSIGNED NOT NULL,
    "name" CHAR(100) NOT NULL,
    "type" CHAR(15) DEFAULT '',
    PRIMARY KEY ("accessMask","type")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
('20160201053352.424')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
