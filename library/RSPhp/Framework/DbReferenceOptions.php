<?php

namespace RSPhp\Framework;
use Exception;

/**
 * Enum for db foreign key reference options
 */
abstract class DbReferenceOptions
{
    const Cascade = "CASCADE";
    const NoAction = "NO ACTION";
    const SetNull = "SET NULL";
    const SetDefault = "SET DEFAULT";
    const Restrict = "RESTRICT";
} // end class DbReferenceOptions
