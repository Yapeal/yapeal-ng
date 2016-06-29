-- Sql/Corp/CreateAccountBalance.sql
-- version 20160629053411.529
CREATE TABLE "{database}"."{table_prefix}corpAccountBalance" (
    "accountID" BIGINT(20) UNSIGNED NOT NULL,
    "accountKey" VARCHAR(255) DEFAULT '',
    "balance" DECIMAL(17, 2) NOT NULL,
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID","accountID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
('20160629053411.529')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
