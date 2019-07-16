

/**
 * User defined function udf_delete_$tableName
 */
CREATE OR REPLACE FUNCTION udf_delete_$tableName($pkParams) RETURNS VOID AS
$$
BEGIN
  DELETE
  FROM
    $tableName
  WHERE
    $pkWhere;
END;
$$ LANGUAGE plpgsql;
