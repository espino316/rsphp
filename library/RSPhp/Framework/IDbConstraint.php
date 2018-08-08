<?php

namespace RSPhp\Framework;

/**
 * Shared methods for DbConstraints
 */
interface IDbConstraint
{
    /**
     * Virtual method to get the actual sql for the constraint
     */
    public function getSql();
} // end interface IDbConstraint
