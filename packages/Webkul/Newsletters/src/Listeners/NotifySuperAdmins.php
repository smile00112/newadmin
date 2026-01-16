<?php

namespace Webkul\Newsletters\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Webkul\Newsletters\Events\AdminCreated;
use Webkul\Newsletters\Mail\NewUserNotification;
use Webkul\User\Repositories\AdminRepository;

class NotifySuperAdmins implements ShouldQueue
{
    /**
     * The name of the queue the job should be sent to.
     *
     * @var string
     */
    public $queue = 'mailing-send';

    /**
     * Create a new listener instance.
     *
     * @param AdminRepository $adminRepository
     * @return void
     */
    public function __construct(
        protected AdminRepository $adminRepository
    ) {}

    /**
     * Handle the event.
     *
     * @param AdminCreated $event
     * @return void
     */
    public function handle(AdminCreated $event): void
    {
        try {
            // Получаем всех супер-администраторов (с permission_type 'all' и без company_id)
            $superAdmins = $this->adminRepository
                ->getModel()
                ->leftJoin('roles', 'admins.role_id', '=', 'roles.id')
                ->where('roles.permission_type', 'all')
                ->whereNull('admins.company_id')
                ->where('admins.status', 1)
                ->select('admins.*')
                ->get();

            foreach ($superAdmins as $superAdmin) {
                try {
                    Mail::to($superAdmin->email)->queue(
                        new NewUserNotification(
                            $event->admin,
                            $event->companyName,
                            $event->plan,
                            $event->password
                        )
                    );
                    Log::info('New user notification queued for admin: ' . $superAdmin->email);
                } catch (\Exception $notificationException) {
                    Log::error('Failed to send new user notification to admin: ' . $superAdmin->email, [
                        'trace' => $notificationException->getTraceAsString(),
                        'admin_id' => $superAdmin->id,
                        'new_user_id' => $event->admin->id
                    ]);
                    // Продолжаем отправку остальным администраторам
                }
            }
        } catch (\Exception $exception) {
            Log::error('Failed to send new user notifications: ' . $exception->getMessage(), [
                'trace' => $exception->getTraceAsString(),
                'new_user_id' => $event->admin->id
            ]);
            // Продолжаем выполнение, даже если уведомления не отправились
        }
    }
}
