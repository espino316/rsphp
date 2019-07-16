
/**
 * Begin stored procedure usp_select_$tableName_bykey
 */
DELIMITER $$
DROP PROCEDURE IF EXISTS `usp_select_$tableName_bykey`$$
CREATE PROCEDURE `usp_select_$tableName_bykey`($pkParams)
THE_PROC:BEGIN

	SELECT
$tableColumns
  FROM
		$tableName
  WHERE
    $pkWhere;

END$$
DELIMITER ;
