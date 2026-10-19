<?php

/**
 * Author      : Chandra
 * Version     : 0.1.0
 * Created     : 17-10-2026
 * Description : Pengujian unit dasar (Unit Test) contoh bawaan PHPUnit.
 *
 * Changelog:
 * - 0.1.0 (17-10-2026): Inisialisasi unit test contoh bawaan PHPUnit.
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_that_true_is_true(): void
    {
        $this->assertTrue(true);
    }
}
