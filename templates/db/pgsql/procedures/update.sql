

/**
 * User defined function udf_update_$tableName
 */
CREATE OR REPLACE FUNCTION udf_update_$tableName($updateParams) RETURNS VOID AS
$$
BEGIN
  UPDATE
    $tableName
  SET
$updateColumns
  WHERE
    $pkWhere;
END;
$$ LANGUAGE plpgsql;
