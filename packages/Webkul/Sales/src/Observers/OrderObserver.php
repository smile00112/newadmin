<?php

namespace Webkul\Sales\Observers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderStatusHistory;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        if (! $order->status) {
            return;
        }

        OrderStatusHistory::create([
            'order_id'   => $order->id,
            'old_status' => null,
            'new_status' => $order->status,
            'user_type'  => $this->resolveUserType(),
            'user_id'    => $this->resolveUserId(),
            'user_name'  => $this->resolveUserName(),
            'source'     => $this->detectSource(),
        ]);
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        if (! $order->wasChanged('status')) {
            return;
        }

        OrderStatusHistory::create([
            'order_id'   => $order->id,
            'old_status' => $order->getOriginal('status'),
            'new_status' => $order->status,
            'user_type'  => $this->resolveUserType(),
            'user_id'    => $this->resolveUserId(),
            'user_name'  => $this->resolveUserName(),
            'source'     => $this->detectSource(),
        ]);
    }

    /**
     * Try to detect the logical source of the change.
     */
    protected function detectSource(): string
    {
        if (app()->runningInConsole()) {
            return 'cron';
        }

        try {
            $request = Request::instance();

            if ($request->is('api/*') || $request->wantsJson()) {
                return 'api';
            }

            $routeName = $request->route()?->getName();

            if ($routeName && str_contains($routeName, 'webhook')) {
                return 'webhook';
            }

            if ($routeName && str_starts_with($routeName, 'admin.')) {
                return 'admin';
            }
        } catch (\Throwable $e) {
            // Fallback to system
        }

        return 'system';
    }

    /**
     * Resolve user type (guard / context).
     */
    protected function resolveUserType(): ?string
    {
        if (Auth::guard('admin')->check()) {
            return 'admin';
        }

        if (Auth::guard('api')->check()) {
            return 'api';
        }

        if (Auth::guard('customer')->check()) {
            return 'customer';
        }

        return null;
    }

    /**
     * Resolve the ID of the acting user, if any.
     */
    protected function resolveUserId(): ?int
    {
        $user = $this->resolveUser();

        return $user?->id;
    }

    /**
     * Resolve a human readable user name, if any.
     */
    protected function resolveUserName(): ?string
    {
        $user = $this->resolveUser();

        if (! $user) {
            return null;
        }

        foreach (['name', 'full_name', 'first_name', 'last_name'] as $attribute) {
            if (isset($user->{$attribute}) && $user->{$attribute}) {
                return (string) $user->{$attribute};
            }
        }

        if (isset($user->email)) {
            return (string) $user->email;
        }

        return (string) $user->getAuthIdentifier();
    }

    /**
     * Resolve the underlying authenticated user instance, if any.
     */
    protected function resolveUser(): ?object
    {
        foreach (['admin', 'api', 'customer', null] as $guard) {
            $user = $guard ? Auth::guard($guard)->user() : Auth::user();

            if ($user) {
                return $user;
            }
        }

        return null;
    }
}

