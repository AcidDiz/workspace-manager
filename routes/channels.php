<?php

use App\Models\Workshop;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('admin.workshop-statistics', function ($user) {
    return $user->can('create', Workshop::class);
});
