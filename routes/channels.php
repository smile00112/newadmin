<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Mailing list channels for real-time updates
Broadcast::channel('mailing-list.{mailingListId}', function ($user, $mailingListId) {
    // Allow all authenticated users to listen to mailing list updates
    return true;
});

Broadcast::channel('mailing-lists-stats', function ($user) {
    // Allow all authenticated users to listen to stats updates
    return true;
});

// Public channel for customer numbers events
Broadcast::channel('customer-numbers', function ($user) {
    // Allow all authenticated users to listen to customer numbers updates
    return true;
});
