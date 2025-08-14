<?php

namespace MattYeend\GuestToUserHelper\Tests;

use PHPUnit\Framework\TestCase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Session;
use MattYeend\GuestToUserHelper\GuestMigrator;
use MattYeend\GuestToUserHelper\Events\GuestMigrating;
use MattYeend\GuestToUserHelper\Events\GuestMigrated;
use Illuminate\Database\Eloquent\Model;

class GuestMigratorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Laravel should already be bootstrapped by the host app running phpunit
    }

    public function test_it_dispatches_events_and_migrates_models()
    {
        // Fake Laravel events
        Event::fake();

        // Simulate a guest token in session
        Session::put('guest_token', 'abc-123');

        // Create a temporary model class for testing
        $modelClass = new class extends Model {
            protected $table = 'test_items';
            public $timestamps = false;
            protected $fillable = ['guest_token', 'user_id'];
        };

        // Insert a fake guest-owned record
        $modelClass->create(['guest_token' => 'abc-123']);

        // Create migrator and run migration
        $migrator = new GuestMigrator([get_class($modelClass)], 'user_id');
        $migrator->migrate(5);

        // Assert the record was updated
        $this->assertEquals(
            1,
            $modelClass->where('user_id', 5)->count(),
            'Guest data should be migrated to the user.'
        );

        // Assert events were fired
        Event::assertDispatched(GuestMigrating::class);
        Event::assertDispatched(GuestMigrated::class);
    }

    public function test_it_does_nothing_if_no_guest_token()
    {
        Event::fake();
        Session::forget('guest_token');

        $migrator = new GuestMigrator([], 'user_id');
        $migrator->migrate(5);

        Event::assertNotDispatched(GuestMigrating::class);
        Event::assertNotDispatched(GuestMigrated::class);
    }
}
