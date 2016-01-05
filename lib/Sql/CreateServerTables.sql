CREATE TABLE "{database}"."{table_prefix}serverServerStatus" (
    "onlinePlayers" BIGINT(20) UNSIGNED NOT NULL,
    "serverName"    CHAR(24)            NOT NULL,
    "serverOpen"    CHAR(5)             NOT NULL,
    PRIMARY KEY ("serverName")
)
ENGINE = { engine}
DEFAULT CHARSET = utf8mb4
COLLATE = utf8mb4_unicode_520_ci;
