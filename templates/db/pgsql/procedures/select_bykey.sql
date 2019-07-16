

/**
 * Procedure udf_select_$tableName_bykey
 */
CREATE OR REPLACE FUNCTION udf_select_$tableName_bykey($pkParams)
  RETURNS TABLE(
    $tableSchema
  ) AS $$
BEGIN
  RETURN QUERY
    SELECT
      $tableColumns
    FROM
      $tableName
    WHERE
      $pkWhere;
END;
$$ LANGUAGE plpgsql;
