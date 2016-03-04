-- Sql/Char/CreateAccountBalance.sql
-- version 20160201053350.800
CREATE TABLE "{database}"."{table_prefix}charAccountBalance" (
    "accountID"  BIGINT(20) UNSIGNED  NOT NULL,
    "accountKey" SMALLINT(5) UNSIGNED NOT NULL,
    "balance"    DECIMAL(17, 2)       NOT NULL,
    "ownerID"    BIGINT(20) UNSIGNED  NOT NULL,
    PRIMARY KEY ("ownerID", "accountID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160201053350.800')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
