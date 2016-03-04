CREATE TABLE "{database}"."{table_prefix}accountAPIKeyInfo" (
    "accessMask" BIGINT(20) UNSIGNED                          NOT NULL,
    "expires"    DATETIME                                     NOT NULL DEFAULT '2038-01-19 03:14:07',
    "keyID"      BIGINT(20) UNSIGNED                          NOT NULL,
    "type"       ENUM ('Account', 'Character', 'Corporation') NOT NULL,
    PRIMARY KEY ("keyID")
);
ALTER TABLE "{database}"."{table_prefix}accountAPIKeyInfo" ADD INDEX "accountAPIKeyInfo1"  ("type");
CREATE TABLE "{database}"."{table_prefix}accountCharacters" (
    "allianceID"      BIGINT(20) UNSIGNED NOT NULL,
    "allianceName"    CHAR(50)            NOT NULL,
    "characterID"     BIGINT(20) UNSIGNED NOT NULL,
    "characterName"   CHAR(50)            NOT NULL,
    "corporationID"   BIGINT(20) UNSIGNED NOT NULL,
    "corporationName" CHAR(50)            NOT NULL,
    "factionID"       BIGINT(20) UNSIGNED NOT NULL,
    "factionName"     CHAR(50)            NOT NULL,
    PRIMARY KEY ("characterID")
);
ALTER TABLE "{database}"."{table_prefix}accountCharacters" ADD INDEX "accountCharacters1"  ("corporationID");
CREATE TABLE "{database}"."{table_prefix}accountKeyBridge" (
    "characterID" BIGINT(20) UNSIGNED NOT NULL,
    "keyID"       BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("keyID", "characterID")
);
ALTER TABLE "{database}"."{table_prefix}accountKeyBridge" ADD UNIQUE INDEX "accountKeyBridge1"  ("characterID", "keyID");
