-- Sql/Corp/CreateAccountBalance.sql
-- version 20160201053351.292
CREATE TABLE "{database}"."{table_prefix}corpAccountBalance" (
    "accountID"  BIGINT(20) UNSIGNED  NOT NULL,
    "accountKey" SMALLINT(5) UNSIGNED NOT NULL,
    "balance"    DECIMAL(17, 2)       NOT NULL,
    "ownerID"    BIGINT(20) UNSIGNED  NOT NULL,
    PRIMARY KEY ("ownerID", "accountID")
);
ALTER TABLE "{database}"."{table_prefix}corpAccountBalance" ADD UNIQUE INDEX "corpAccountBalance1"  ("ownerID", "accountKey");
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160201053351.292')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
