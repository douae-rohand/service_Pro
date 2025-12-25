<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('intervenant.{id}', function ($user, $id) {
    \Illuminate\Support\Facades\Log::info("Broadcast Auth Check: User {$user->id} attempting to subscribe to intervenant.{$id}");
    return (int) $user->id === (int) $id;
});
