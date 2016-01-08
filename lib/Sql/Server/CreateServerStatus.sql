-- Sql/Server/CreateServerStatus.sql
-- version 201601080354
CREATE TABLE "{database}"."{table_prefix}serverServerStatus" (
    "onlinePlayers" TEXT NOT NULL,
    "serverOpen" TEXT NOT NULL
)
ENGINE = {engine}
DEFAULT CHARSET = utf8mb4
COLLATE = utf8mb4_unicode_520_ci;
