<?php
/**
 * Copyright © 2017 SeQura Engineering. All rights reserved.
 */

namespace Sequra\Card\Api;

interface CardQueryInterface
{
    /**
     * Starts a stored cards query
     *
     * @return string form.
     */
    public function startQuery();

    /**
     * Returns query results if ready
     *
     * @return string form.
     */
    public function getQueryResult();
}
