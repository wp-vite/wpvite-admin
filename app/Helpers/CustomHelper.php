<?php

namespace App\Helpers;

use Faker\Factory as Faker;
use Illuminate\Support\Str;

class CustomHelper
{
    /**
     * Generate Hex Id
     * @param string $prefix
     * @param bool $randomSuffix Make more unique
     * @param string $case upper|lower
     * @return string
     */
    public static function generateHexId(string $prefix = '', bool $randomSuffix = true, string $case = 'upper')
    {
        $timestamp = dechex((int) (microtime(true) * 1000)); // Millisecond precision
        $id = $prefix . $timestamp;

        if($randomSuffix) {
            $id .= Str::random(4);
        }

        return ($case == 'lower') ? strtolower($id) : strtoupper($id);
    }

    /**
     * Generate Username
     * @param int $maxLength
     * @param bool $randomSuffix
     * @return string
     */
    public static function generateUsername(int $maxLength = 12, string $prefix = '')
    {
        $faker = Faker::create();
        $nb = round(max([$maxLength / 5, 1]), 0);

        do {
            $username = $prefix . ucfirst($faker->userName);
            $username .= ucwords($faker->words($nb, true));

            // Filter out non-alphanumeric characters
            $username = preg_replace('/[^a-zA-Z0-9]/', '', $username);

            // Truncate the username if it exceeds the max length
            if (strlen($username) > $maxLength) {
                $username = substr($username, 0, $maxLength);
            }
        } while (empty($username)); // Ensure a valid username is generated

        return $username;
    }
}
