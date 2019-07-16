
/**
 * Procedure udf_select_$tableName
 */
CREATE OR REPLACE FUNCTION udf_select_$tableName()
  RETURNS TABLE(
    $tableSchema
  ) AS $$
BEGIN
  RETURN QUERY
    SELECT
$tableColumns
    FROM
      $tableName;
END;
$$ LANGUAGE plpgsql;
