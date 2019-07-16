
/**
 * Begin stored procedure usp_update_$tableName
 */
DELIMITER $$
DROP PROCEDURE IF EXISTS `usp_update_$tableName`$$
CREATE PROCEDURE `usp_update_$tableName`($updateParams)
THE_PROC:BEGIN

  UPDATE
    $tableName
  SET
$updateColumns
  WHERE
    $pkWhere;

END$$
DELIMITER ;
