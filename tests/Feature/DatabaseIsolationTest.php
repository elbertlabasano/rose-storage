<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use LogicException;
use Tests\TestCase;

class DatabaseIsolationTest extends TestCase
{
    public function test_booted_application_has_only_an_in_memory_database(): void
    {
        $this->assertSame('testing', app()->environment());
        $this->assertFalse(app()->configurationIsCached());
        $this->assertSame(['sqlite'], array_keys(config('database.connections')));
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
    }

    public function test_explicit_mysql_connection_is_not_configured(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        DB::connection('mysql');
    }

    public function test_adding_mysql_configuration_cannot_bypass_the_guard(): void
    {
        config(['database.connections.unsafe' => [
            'driver' => 'mysql', 'host' => '127.0.0.1', 'database' => 'rose_storage', 'username' => 'root',
        ]]);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Persistent database access is blocked');
        DB::connection('unsafe');
    }

    public function test_disk_backed_sqlite_is_also_blocked(): void
    {
        config(['database.connections.unsafe' => ['driver' => 'sqlite', 'database' => database_path('database.sqlite')]]);
        $this->expectException(LogicException::class);
        DB::connection('unsafe');
    }

    public function test_database_url_cannot_redirect_a_memory_connection(): void
    {
        config(['database.connections.unsafe' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'url' => 'mysql://root@127.0.0.1/rose_storage',
        ]]);
        $this->expectException(LogicException::class);
        DB::connection('unsafe');
    }
}
