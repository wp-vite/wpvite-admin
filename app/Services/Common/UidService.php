<?php
namespace App\Services\Common;

use Carbon\Carbon;
use Exception;

class UidService
{
    const PREFIX_LENGTH = 2; // Never change
    const TIMESTAMP_LENGTH = 8; // Never change, it can hold the time until 2059-05-25 17:38:27 in Base36 format
    const RANDOM_SUFFIX_LENGTH = 5; // (60+ million options) Should not be changed unless absolute necessary

    /**
     * Generate a globally unique, sortable, fixed-length (15 char) Unique ID.
     * @param string $prefix
     * @return string
     */
    public static function generate(string $prefix): string
    {
        $prefix = str_pad(substr($prefix, 0, self::PREFIX_LENGTH), self::PREFIX_LENGTH, '0', STR_PAD_RIGHT);

        $timestamp = (int)(microtime(true) * 1000);
        $timestampBase36 = str_pad(self::base10to36($timestamp), self::TIMESTAMP_LENGTH, '0', STR_PAD_LEFT);

        $random = str_pad(self::base10to36(random_int(0, 60466175)), self::RANDOM_SUFFIX_LENGTH, '0', STR_PAD_LEFT); // 36^5

        return strtoupper($prefix . $timestampBase36 . $random);
    }

    /**
     * Extract prefix from uid.
     * @param string $uid
     * @return string
     * @throws Exception
     */
    public static function prefix(string $uid): string
    {
        if(!self::isValid($uid)) {
            throw new Exception('Invalid uid.');
        }
        return rtrim(substr($uid, 0, self::PREFIX_LENGTH), 0);
    }

    /**
     * Extract timestamp (Carbon instance) from uid.
     * @param string $uid
     * @return Carbon
     * @throws Exception
     */
    public static function timestamp(string $uid): Carbon
    {
        if(!self::isValid($uid)) {
            throw new Exception('Invalid uid.');
        }
        $timestampSegment = substr($uid, 2, self::TIMESTAMP_LENGTH);
        $timestampMillis = self::base36to10($timestampSegment);

        return Carbon::createFromTimestampMs($timestampMillis);
    }

    /**
     * Validate the format of a uid.
     * @param string $uid
     * @return bool
     */
    public static function isValid(string $uid): bool
    {
        return preg_match('/^[0-9A-Z]{15}$/', strtoupper($uid)) === 1;
    }

    /**
     * Summary of base10to36
     * @param int $number
     * @return string
     */
    private static function base10to36(int $number): string
    {
        return base_convert($number, 10, 36);
    }

    /**
     * Summary of base36to10
     * @param string $string
     * @return int
     */
    private static function base36to10(string $string): int
    {
        return (int)base_convert($string, 36, 10);
    }
}
