<?php

namespace Tests\Unit;

use App\Models\Siswa;
use Tests\TestCase;

class SiswaModelTest extends TestCase
{
    public function test_uses_the_correct_table_name_for_authentication(): void
    {
        $this->assertSame('siswa', (new Siswa())->getTable());
    }
}
