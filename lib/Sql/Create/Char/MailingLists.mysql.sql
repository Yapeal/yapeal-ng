-- Sql/Create/Char/MailingLists.sql
-- version 20161129113301.038
CREATE TABLE "{schema}"."{tablePrefix}charMailingLists" (
    "displayName" CHAR(100)           NOT NULL,
    "listID"      BIGINT(20) UNSIGNED NOT NULL,
    "ownerID"     BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID", "listID")
);
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161129113301.038');
COMMIT;
