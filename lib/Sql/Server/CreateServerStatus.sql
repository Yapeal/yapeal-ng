-- Sql/Server/CreateServerStatus.sql
-- version 201601110622
CREATE TABLE "{database}"."{table_prefix}serverServerStatus" (
    "onlinePlayers" TEXT NOT NULL,
    "serverOpen" TEXT NOT NULL);
