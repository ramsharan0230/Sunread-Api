<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PreparingTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        if ( app()->environment() !== "ci" ) {
            Schema::disableForeignKeyConstraints();
            $this->artisan("migrate:fresh");
            $this->artisan("db:seed", ["--force" => true]);
        }
    }

    public function testDbHasTables(): void
    {
        $tables = DB::select('SHOW TABLES');
        $this->assertTrue(count($tables) > 0);
    }
}
