-- Sql/Char/CreateAccountBalance.sql
-- version 20160629053410.987
CREATE TABLE "{schema}"."{tablePrefix}charAccountBalance" (
    "accountID"  BIGINT(20) UNSIGNED  NOT NULL,
    "accountKey" SMALLINT(5) UNSIGNED NOT NULL,
    "balance"    DECIMAL(17, 2)       NOT NULL,
    "ownerID"    BIGINT(20) UNSIGNED  NOT NULL,
    PRIMARY KEY ("ownerID", "accountID")
);
ALTER TABLE "{schema}"."{tablePrefix}charAccountBalance"
    ADD UNIQUE INDEX "{tablePrefix}AccountBalance1"  ("ownerID", "accountKey");
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}utilDatabaseVersion" ("version")
    VALUES ('20160629053410.987')
    ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
