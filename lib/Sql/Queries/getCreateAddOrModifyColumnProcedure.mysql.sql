-- Sql/Queries/getCreateAddOrModifyColumnProcedure.mysql.sql
-- version 20161129051749.109
-- @formatter:off
CREATE PROCEDURE "{schema}"."AddOrModifyColumn"(
 IN param_database_name  VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci,
 IN param_table_name     VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci,
 IN param_column_name    VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci,
 IN param_column_details VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci)
 BEGIN
 IF NOT EXISTS(SELECT NULL
 FROM "information_schema"."COLUMNS"
 WHERE
 "COLUMN_NAME" COLLATE utf8_unicode_ci = param_column_name
 AND "TABLE_NAME" COLLATE utf8_unicode_ci = param_table_name
 AND "table_schema" COLLATE utf8_unicode_ci = param_database_name)
 THEN
 SET @StatementToExecute = concat('ALTER TABLE "', param_database_name, '"."', param_table_name,
 '" ADD COLUMN "', param_column_name, '" ', param_column_details){semiColon}
 PREPARE DynamicStatement FROM @StatementToExecute{semiColon}
 EXECUTE DynamicStatement{semiColon}
 DEALLOCATE PREPARE DynamicStatement{semiColon}
 ELSE
 SET @StatementToExecute = concat('ALTER TABLE "', param_database_name, '"."', param_table_name,
 '" MODIFY COLUMN "', param_column_name, '" ', param_column_details){semiColon}
 PREPARE DynamicStatement FROM @StatementToExecute{semiColon}
 EXECUTE DynamicStatement{semiColon}
 DEALLOCATE PREPARE DynamicStatement{semiColon}
 END IF{semiColon}
 END;
