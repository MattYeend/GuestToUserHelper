<?php

namespace MattYeend\GuestToUserHelper\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasGuestOwnership
{
    public static function bootHasGuestOwnership(): void
    {
        static::creating(function ($model) {
            $userKey = property_exists($model, 'guestUserKey')
                ? $model->guestUserKey
                : config('guest-upgrade.user_foreign_key', 'user_id');

            if (auth()->check()) {
                $model->{$userKey} = auth()->id();
                $model->guest_token = null;
            } else {
                $model->guest_token = session(config('guest-upgrade.session_key'));
            }
        });
    }

    public function scopeOwnedByGuest(Builder $query, string $guestToken): Builder
    {
        return $query->where('guest_token', $guestToken);
    }
}
