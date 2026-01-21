<?php

namespace Illuminate\Support\Facades;

/**
 * Minimal stub for IDE/static analysis in this repository.
 * The real implementation is provided by Laravel framework via Composer.
 */
class Redis
{
    /**
     * @return mixed
     */
    public static function connection(?string $name = null)
    {
        return new class {
            /**
             * @return mixed
             */
            public function ping()
            {
                return 'PONG';
            }
        };
    }
}

