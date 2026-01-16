<?php

namespace Webkul\Newsletters\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Webkul\User\Contracts\Admin;

class AdminCreated
{
    use Dispatchable, SerializesModels;

    /**
     * The created admin user.
     *
     * @var Admin
     */
    public Admin $admin;

    /**
     * The company name.
     *
     * @var string
     */
    public string $companyName;

    /**
     * The plan name.
     *
     * @var string
     */
    public string $plan;

    /**
     * The password for notification.
     *
     * @var string
     */
    public string $password;

    /**
     * Create a new event instance.
     *
     * @param Admin $admin
     * @param string $companyName
     * @param string $plan
     * @param string $password
     * @return void
     */
    public function __construct(Admin $admin, string $companyName, string $plan, string $password)
    {
        $this->admin = $admin;
        $this->companyName = $companyName;
        $this->plan = $plan;
        $this->password = $password;
    }
}
