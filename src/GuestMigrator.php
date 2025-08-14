<?php

namespace MattYeend\GuestToUserHelper;

use Illuminate\Contracts\Events\Dispatcher;
use MattYeend\GuestToUserHelper\Events\GuestMigrated;
use MattYeend\GuestToUserHelper\Events\GuestMigrating;

class GuestMigrator
{
    protected array $models;
    protected string $userKey;

    public function __construct(array $models, string $userKey = 'user_id')
    {
        $this->models = $models;
        $this->userKey = $userKey;
    }

    public function token(): ?string
    {
        return session()->get(config('guest-upgrade.session_key'));
    }

    public function migrate(int|string $userId, ?string $guestToken = null): void
    {
        $token = $guestToken ?? $this->token();
        if (!$token) return;

        $events = app(Dispatcher::class);
        $events->dispatch(new GuestMigrating($userId, $token));

        foreach ($this->models as $modelClass) {
            $instance = new $modelClass();
            $userKey = property_exists($instance, 'guestUserKey') ? $instance->guestUserKey : $this->userKey;

            $modelClass::where('guest_token', $token)
                ->update([$userKey => $userId, 'guest_token' => null]);
        }

        session()->forget(config('guest-upgrade.session_key'));
        $events->dispatch(new GuestMigrated($userId));
    }
}
