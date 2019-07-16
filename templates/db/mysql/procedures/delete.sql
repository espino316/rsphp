
/**
 * Begin stored procedure usp_delete_$tableName
 */
DELIMITER $$
DROP PROCEDURE IF EXISTS `usp_delete_$tableName`$$
CREATE PROCEDURE `usp_delete_$tableName`($pkParams)
THE_PROC:BEGIN

  DELETE
  FROM
    $tableName
  WHERE
    $pkWhere;

END$$
DELIMITER ;
