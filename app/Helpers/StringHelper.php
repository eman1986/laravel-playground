<?php

namespace App\Helpers;

use LengthException;

class StringHelper
{
    /**
     * Generates a random hexadecimal code of the specified length.
     *
     * @param  int  $length  The desired length of the generated code. Must be an even number.
     * @return string A randomly generated hexadecimal string.
     *
     * @throws LengthException If the provided length is not an even number.
     * @throws \Random\RandomException
     */
    public static function generateRandomCode(int $length = 8): string
    {
        if ($length % 2 !== 0) {
            throw new LengthException('length must be an even number.');
        }

        return bin2hex(random_bytes($length));
    }
}
