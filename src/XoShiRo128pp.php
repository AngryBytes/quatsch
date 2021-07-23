<?php
namespace Quatsch;

// PHP integers are signed, and of platform-dependant size. To emulate 32-bit
// unsigned math, we need at least one extra bit for the (unused) sign bit.
//
// TODO: I think we can overcome this, but we need an automated test of this somehow.
// - 32-bit `>>` in rotl may insert zeroes if the sign bit is set.
// - Return value should probably become float `[0, 1)`, to be consistent.
assert(PHP_INT_SIZE >= 5);

/**
 * Implementation of xoshiro128++ pseudo-random number generator in pure PHP.
 *
 * Original C implementation by David Blackman and Sebastiano Vigna, licensed CC0.
 * https://prng.di.unimi.it/xoshiro128plusplus.c
 */
final class XoShiRo128pp
{
    /**
     * Maximum value returned by the generator.
     */
    public const MAX = 0xffffffff;

    /** @var int[] */
    private array $state;

    /**
     * Initialize a generator with raw state.
     *
     * State must not be all zeroes. Values are truncated to the range `[0, MAX]`.
     */
    public function __construct(int $s0, int $s1, int $s2, int $s3)
    {
        $this->state = [
            self::MAX & $s0,
            self::MAX & $s1,
            self::MAX & $s2,
            self::MAX & $s3,
        ];
    }

    /**
     * Initialize a generator with a random seed based on `rand()`.
     *
     * Optionally takes a seed, in which case `srand()` will be called first.
     */
    public static function from_rand(int $seed = null): XoShiRo128pp
    {
        if ($seed !== null) {
            srand($seed);
        }

        return new XoShiRo128pp(
            rand(0, self::MAX),
            rand(0, self::MAX),
            rand(0, self::MAX),
            rand(0, self::MAX),
        );
    }

    /**
     * Return the next random number from the generator.
     *
     * The returned value is in the range `[0, MAX]`.
     */
    public function next(): int
    {
        $s = &$this->state;
        $result = self::MAX & (self::rotl(self::MAX & ($s[0] + $s[3]), 7) + $s[0]);

        $t = self::MAX & ($s[1] << 9);

        $s[2] ^= $s[0];
        $s[3] ^= $s[1];
        $s[1] ^= $s[2];
        $s[0] ^= $s[3];

        $s[2] ^= $t;

        $s[3] = self::rotl($s[3], 11);

        return $result;
    }

    private static function rotl(int $x, int $k): int
    {
        return self::MAX & (($x << $k) | ($x >> (32 - $k)));
    }
}
