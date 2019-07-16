DELIMITER $$
DROP PROCEDURE IF EXISTS `usp_select_$tableName`$$
CREATE PROCEDURE `usp_select_$tableName`()
THE_PROC:BEGIN

	SELECT
    $tableColumns
  FROM
		$tableName;

END$$
DELIMITER ;
