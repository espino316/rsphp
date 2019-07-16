
/**
 * User defined function udf_insert_$tableName
 */
CREATE OR REPLACE FUNCTION udf_insert_$tableName($insertParams) RETURNS VOID AS
$$
BEGIN
    INSERT INTO
      $tableName (
        $tableColumns
      ) VALUES (
        $paramsNames
      );
END;
$$ LANGUAGE plpgsql;
