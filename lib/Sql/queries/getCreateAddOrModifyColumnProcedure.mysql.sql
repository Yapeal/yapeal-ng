-- Sql/queries/getCreateAddOrModifyColumnProcedure.mysql.sql
-- version 20160810085257.100
-- noinspection SqlResolveForFile
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
 /* Create the full statement to execute */
 SET @StatementToExecute = concat('ALTER TABLE "', param_database_name, '"."', param_table_name,
 '" ADD COLUMN "', param_column_name, '" ', param_column_details) $$
 /* Prepare and execute the statement that was built */
 PREPARE DynamicStatement FROM @StatementToExecute$$
 EXECUTE DynamicStatement$$
 /* Cleanup the prepared statement */
 DEALLOCATE PREPARE DynamicStatement$$
 ELSE
 /* Create the full statement to execute */
 SET @StatementToExecute = concat('ALTER TABLE "', param_database_name, '"."', param_table_name,
 '" MODIFY COLUMN "', param_column_name, '" ', param_column_details) $$
 /* Prepare and execute the statement that was built */
 PREPARE DynamicStatement FROM @StatementToExecute$$
 EXECUTE DynamicStatement$$
 /* Cleanup the prepared statement */
 DEALLOCATE PREPARE DynamicStatement$$
 END IF$$
 END;
