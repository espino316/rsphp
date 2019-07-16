
/**
 * Insert $tableName procedure
 */
DELIMITER $$
DROP PROCEDURE IF EXISTS `usp_insert_$tableName`$$
CREATE PROCEDURE `usp_insert_$tableName`(
$insertParams
)
THE_PROC:BEGIN

  INSERT INTO
    $tableName (
$tableColumns
    )
  VALUES (
$paramsNames
  );

END$$
DELIMITER ;
