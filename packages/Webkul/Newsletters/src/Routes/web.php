<?php

use Illuminate\Support\Facades\Route;
use Webkul\Newsletters\Http\Controllers\Admin\HooksController;
use Webkul\Newsletters\Http\Controllers\Admin\VacapInstanceController;
use Webkul\Newsletters\Http\Controllers\Admin\ChannelInstancesController;
use Webkul\Newsletters\Http\Controllers\Admin\MailingListController;
use Webkul\Newsletters\Http\Controllers\Admin\CustomerNumberController;
use Webkul\Newsletters\Http\Controllers\Admin\StopListController;
use Webkul\Newsletters\Http\Controllers\Admin\UnifiedNewsletterController;
use Webkul\Newsletters\Http\Controllers\Admin\ContactGroupController;
use Webkul\Newsletters\Http\Controllers\Admin\ContactFilterController;
use Webkul\Newsletters\Http\Controllers\Admin\ContactController;
use Webkul\Newsletters\Http\Controllers\Admin\ReportsController;
use Webkul\Newsletters\Http\Controllers\Admin\ManagerController;
use Webkul\Newsletters\Http\Controllers\Admin\AccountController;
use Webkul\Newsletters\Http\Controllers\Admin\AdminAccountController;
use Webkul\Newsletters\Http\Controllers\Admin\OwnersController;
use Webkul\Newsletters\Http\Controllers\Admin\RegistrationRequestController;
use Webkul\Newsletters\Http\Controllers\LandingPageController;

/**
 * Public landing page routes.
 */
Route::group(['prefix' => 'mailing-service'], function () {
    Route::controller(LandingPageController::class)->group(function () {
        Route::get('', 'index')->name('newsletters.landing.index');
        Route::post('register', 'store')->name('newsletters.landing.register');
        Route::get('payment-terms', 'paymentTerms')->name('newsletters.landing.payment-terms');
        Route::get('privacy-policy', 'privacyPolicy')->name('newsletters.landing.privacy-policy');
        Route::get('offer', function () {
            $pathToFile = $_SERVER['DOCUMENT_ROOT'] . '/files/oferta_TargetX.pdf';
            return response()->download($pathToFile, 'Лицензионная публичная оферта TargetX.pdf');
          // return redirect()->away('https://oferta.targetx.su');
        })->name('newsletters.landing.offer');
        Route::get('activate/{token}', 'activateAccount')->name('newsletters.landing.activate');
    });
});

//TODO add custom middleware to greenapi webhook routes
Route::group(['prefix' => 'newsletters'], function () {
    Route::controller(HooksController::class)->prefix('hook')->group(function () {
        Route::get('webhook', 'get_hook')->name('admin.newsletters.hook_g');
        Route::post('webhook', 'get_hook')->name('admin.newsletters.hook');
        Route::get('test-broadcast/{id}', 'testBroadcastStatsUpdate')->name('admin.newsletters.hook.test_broadcast');
    });
});

Route::group([
    'prefix' => 'admin/newsletters',
    'middleware' => ['web', 'admin', 'newsletters.company']
], function () {

    /**
     * Test route to verify module is working.
     */
    Route::get('test', function() {
        return 'Newsletters module is working!';
    })->name('admin.newsletters.test');

    /**
     * Administration routes (for super admins with permission_type = all).
     */
    Route::middleware('newsletters.permission:administration')->group(function () {
        /**
         * Companies routes (for super admins).
         */
        Route::middleware('newsletters.permission:newsletters.companies')->group(function () {
            Route::controller(\Webkul\Newsletters\Http\Controllers\Admin\CompanyController::class)->prefix('companies')->group(function () {
                Route::get('', 'index')->name('admin.newsletters.companies.index');
                Route::middleware('newsletters.permission:newsletters.companies.create')->group(function () {
                    Route::get('create', 'create')->name('admin.newsletters.companies.create');
                    Route::post('create', 'store')->name('admin.newsletters.companies.store');
                });
                Route::middleware('newsletters.permission:newsletters.companies.edit')->group(function () {
                    Route::get('edit/{id}', 'edit')->name('admin.newsletters.companies.edit');
                    Route::put('edit/{id}', 'update')->name('admin.newsletters.companies.update');
                });
                Route::middleware('newsletters.permission:newsletters.companies.delete')->group(function () {
                    Route::delete('{id}', 'destroy')->name('admin.newsletters.companies.destroy');
                });
            });
        });

        /**
         * Owners management routes (for super admins only).
         */
        Route::middleware('newsletters.permission:newsletters.owners.view')->group(function () {
            Route::controller(OwnersController::class)->prefix('owners')->group(function () {
                Route::get('', 'index')->name('admin.newsletters.owners.index');
                Route::middleware('newsletters.permission:newsletters.owners.create')->group(function () {
                    Route::get('create', 'create')->name('admin.newsletters.owners.create');
                    Route::post('create', 'store')->name('admin.newsletters.owners.store');
                });
                Route::middleware('newsletters.permission:newsletters.owners.edit')->group(function () {
                    Route::get('edit/{id}', 'edit')->name('admin.newsletters.owners.edit');
                    Route::put('edit/{id}', 'update')->name('admin.newsletters.owners.update');
                    Route::post('{id}/resend-email', 'resendRegistrationEmail')->name('admin.newsletters.owners.resend-email');
                });
                Route::middleware('newsletters.permission:newsletters.owners.toggle-status')->group(function () {
                    Route::post('{id}/toggle-status', 'toggleStatus')->name('admin.newsletters.owners.toggle-status');
                });
                Route::middleware('newsletters.permission:newsletters.owners.topup')->group(function () {
                    Route::post('{id}/topup', 'topup')->name('admin.newsletters.owners.topup');
                });
                Route::middleware('newsletters.permission:newsletters.owners.delete')->group(function () {
                    Route::delete('{id}', 'destroy')->name('admin.newsletters.owners.delete');
                });
                // Impersonation routes
                Route::post('{id}/impersonate', 'impersonate')->name('admin.newsletters.owners.impersonate');
            });
        });

        /**
         * Registration requests routes (for super admins only).
         */
        Route::middleware('newsletters.permission:newsletters.registration-requests.view')->group(function () {
            Route::controller(RegistrationRequestController::class)->prefix('registration-requests')->group(function () {
                Route::get('', 'index')->name('admin.newsletters.registration-requests.index');
                Route::middleware('newsletters.permission:newsletters.registration-requests.edit')->group(function () {
                    Route::get('edit/{id}', 'edit')->name('admin.newsletters.registration-requests.edit');
                    Route::put('edit/{id}', 'update')->name('admin.newsletters.registration-requests.update');
                });
                Route::middleware('newsletters.permission:newsletters.registration-requests.delete')->group(function () {
                    Route::delete('{id}', 'destroy')->name('admin.newsletters.registration-requests.destroy');
                });
            });
        });
    });

    /**
     * Managers routes (only for owners).
     */
    Route::middleware('newsletters.permission:newsletters.managers')->group(function () {
        Route::controller(ManagerController::class)->prefix('managers')->group(function () {
            Route::get('', 'index')->name('admin.newsletters.managers.index');
            Route::middleware('newsletters.permission:newsletters.managers.create')->group(function () {
                Route::get('create', 'create')->name('admin.newsletters.managers.create');
                Route::post('create', 'store')->name('admin.newsletters.managers.store');
            });
            Route::middleware('newsletters.permission:newsletters.managers.edit')->group(function () {
                Route::get('edit/{id}', 'edit')->name('admin.newsletters.managers.edit');
                Route::put('edit/{id}', 'update')->name('admin.newsletters.managers.update');
                Route::post('{id}/permissions', 'updatePermissions')->name('admin.newsletters.managers.update-permissions');
            });
            Route::middleware('newsletters.permission:newsletters.managers.delete')->group(function () {
                Route::delete('{id}', 'destroy')->name('admin.newsletters.managers.destroy');
            });
        });
    });


    /**
     * Vacap Instances routes.
     */
    Route::middleware('newsletters.permission:newsletters.whatsapp-instances')->group(function () {
        Route::controller(VacapInstanceController::class)->prefix('whatsapp-instances')->group(function () {
            Route::get('', 'index')->name('admin.newsletters.whatsapp-instances.index');
            Route::middleware('newsletters.permission:newsletters.whatsapp-instances.create')->group(function () {
                Route::get('create', 'create')->name('admin.newsletters.whatsapp-instances.create');
                Route::post('create', 'store')->name('admin.newsletters.whatsapp-instances.store');
            });
            Route::middleware('newsletters.permission:newsletters.whatsapp-instances.edit')->group(function () {
                Route::get('edit/{id}', 'edit')->name('admin.newsletters.whatsapp-instances.edit');
                Route::put('edit/{id}', 'update')->name('admin.newsletters.whatsapp-instances.update');
            });
            Route::middleware('newsletters.permission:newsletters.whatsapp-instances.delete')->group(function () {
                Route::delete('{id}', 'destroy')->name('admin.newsletters.whatsapp-instances.destroy');
            });
        });
    });

    /**
     * Channel Instances routes (unified management for WhatsApp, Email, Telegram).
     */
    Route::middleware('newsletters.permission:newsletters.channel-instances')->group(function () {
        Route::controller(ChannelInstancesController::class)->prefix('channel-instances')->group(function () {
            Route::get('', 'index')->name('admin.newsletters.channel-instances.index');
            Route::middleware('newsletters.permission:newsletters.channel-instances.create')->group(function () {
                Route::get('create/{type}', 'create')->name('admin.newsletters.channel-instances.create');
                Route::post('create/{type}', 'store')->name('admin.newsletters.channel-instances.store');
            });
            Route::middleware('newsletters.permission:newsletters.channel-instances.edit')->group(function () {
                Route::get('edit/{type}/{id}', 'edit')->name('admin.newsletters.channel-instances.edit');
                Route::put('edit/{type}/{id}', 'update')->name('admin.newsletters.channel-instances.update');
            });
            Route::middleware('newsletters.permission:newsletters.channel-instances.delete')->group(function () {
                Route::delete('{type}/{id}', 'destroy')->name('admin.newsletters.channel-instances.destroy');
            });
        });
    });

    /**
     * Mailing Lists routes.
     */
    Route::middleware('newsletters.permission:newsletters.mailing-lists')->group(function () {
        Route::controller(MailingListController::class)->prefix('mailing-lists')->group(function () {
            Route::get('', 'index')->name('admin.newsletters.mailing-lists.index');
            Route::middleware('newsletters.permission:newsletters.mailing-lists.create')->group(function () {
                Route::get('create', 'create')->name('admin.newsletters.mailing-lists.create');
                Route::post('create', 'store')->name('admin.newsletters.mailing-lists.store');
            });
            Route::middleware('newsletters.permission:newsletters.mailing-lists.edit')->group(function () {
                Route::get('edit/{id}', 'edit')->name('admin.newsletters.mailing-lists.edit');
                Route::put('edit/{id}', 'update')->name('admin.newsletters.mailing-lists.update');
            });
            Route::middleware('newsletters.permission:newsletters.mailing-lists.delete')->group(function () {
                Route::delete('{id}', 'destroy')->name('admin.newsletters.mailing-lists.destroy');
            });
            Route::middleware(['newsletters.permission:newsletters.mailing-lists.send', 'newsletters.account.balance'])->group(function () {
                Route::post('{id}/send', 'send')->name('admin.newsletters.mailing-lists.send');
                Route::post('{id}/start', 'startMailing')->name('admin.newsletters.mailing-lists.start');
                Route::post('{id}/pause', 'pauseMailing')->name('admin.newsletters.mailing-lists.pause');
            });
        });
    });

    Route::controller(MailingListController::class)->prefix('test')->group(function () {
        Route::get('{id}/test', 'testMailing')->name('admin.newsletters.mailing-lists.test');
    });
    /**
     * Customer Numbers routes.
     */
    Route::middleware('newsletters.permission:newsletters.customer-numbers')->group(function () {
        Route::controller(CustomerNumberController::class)->prefix('customer-numbers')->group(function () {
            Route::get('', 'index')->name('admin.newsletters.customer-numbers.index');
            Route::middleware('newsletters.permission:newsletters.customer-numbers.create')->group(function () {
                Route::get('create', 'create')->name('admin.newsletters.customer-numbers.create');
                Route::post('create', 'store')->name('admin.newsletters.customer-numbers.store');
            });
            Route::middleware('newsletters.permission:newsletters.customer-numbers.edit')->group(function () {
                Route::get('edit/{id}', 'edit')->name('admin.newsletters.customer-numbers.edit');
                Route::put('edit/{id}', 'update')->name('admin.newsletters.customer-numbers.update');
            });
            Route::middleware('newsletters.permission:newsletters.customer-numbers.delete')->group(function () {
                Route::delete('{id}', 'destroy')->name('admin.newsletters.customer-numbers.destroy');
            });
            Route::middleware('newsletters.permission:newsletters.customer-numbers.import')->group(function () {
                Route::post('import', 'import')->name('admin.newsletters.customer-numbers.import');
            });
            Route::post('chat-history', 'getChatHistory')->name('admin.newsletters.customer-numbers.chat-history');
            Route::post('search', 'search')->name('admin.newsletters.customer-numbers.search');
            Route::middleware('newsletters.permission:newsletters.messages.send')->group(function () {
                Route::post('send-reply', 'sendReply')->name('admin.newsletters.customer-numbers.send-reply');
            });
        });
    });

    /**
     * Stop List routes.
     */
    Route::middleware('newsletters.permission:newsletters.stop-list')->group(function () {
        Route::controller(StopListController::class)->prefix('stop-list')->group(function () {
            Route::get('', 'index')->name('admin.newsletters.stop-list.index');
            Route::middleware('newsletters.permission:newsletters.stop-list.create')->group(function () {
                Route::get('create', 'create')->name('admin.newsletters.stop-list.create');
                Route::post('create', 'store')->name('admin.newsletters.stop-list.store');
            });
            Route::middleware('newsletters.permission:newsletters.stop-list.edit')->group(function () {
                Route::get('edit/{id}', 'edit')->name('admin.newsletters.stop-list.edit');
                Route::put('edit/{id}', 'update')->name('admin.newsletters.stop-list.update');
            });
            Route::middleware('newsletters.permission:newsletters.stop-list.delete')->group(function () {
                Route::delete('destroy-all', 'destroyAll')->name('admin.newsletters.stop-list.destroy-all');
                Route::post('mass-destroy', 'massDestroy')->name('admin.newsletters.stop-list.mass-destroy');
                Route::delete('{id}', 'destroy')->name('admin.newsletters.stop-list.destroy');
            });
            Route::post('check', 'check')->name('admin.newsletters.stop-list.check');
        });
    });

    /**
     * Messages routes.
     */
    Route::middleware('newsletters.permission:newsletters.messages.view')->group(function () {
        Route::controller(CustomerNumberController::class)->prefix('messages')->group(function () {
            Route::get('', 'messages')->name('admin.newsletters.messages.index');
        });
    });

    /**
     * Unified Newsletter Management routes.
     */
    Route::controller(UnifiedNewsletterController::class)->prefix('unified')->group(function () {
        Route::get('', 'index')->name('admin.newsletters.unified.index');
        Route::post('', 'store')->name('admin.newsletters.unified.store');
        Route::get('edit/{id}', 'edit')->name('admin.newsletters.unified.edit');
        Route::put('edit/{id}', 'update')->name('admin.newsletters.unified.update');
        Route::delete('{id}', 'destroy')->name('admin.newsletters.unified.destroy');
    });

    /**
     * Contact Groups routes.
     */
    Route::middleware('newsletters.permission:newsletters.contact-groups')->group(function () {
        Route::controller(ContactGroupController::class)->prefix('contact-groups')->group(function () {
            Route::get('', 'index')->name('admin.newsletters.contact-groups.index');
            Route::middleware('newsletters.permission:newsletters.contact-groups.create')->group(function () {
                Route::get('create', 'create')->name('admin.newsletters.contact-groups.create');
                Route::post('create', 'store')->name('admin.newsletters.contact-groups.store');
            });
            Route::post('csv/preview', 'previewCsv')->name('admin.newsletters.contact-groups.csv.preview');
            Route::middleware('newsletters.permission:newsletters.contact-groups.edit')->group(function () {
                Route::get('edit/{id}', 'edit')->name('admin.newsletters.contact-groups.edit');
                Route::put('edit/{id}', 'update')->name('admin.newsletters.contact-groups.update');
            });
            
            // Contact Filters routes
            Route::middleware('newsletters.permission:newsletters.contact-groups.edit')->group(function () {
                Route::controller(ContactFilterController::class)->prefix('{groupId}/filters')->group(function () {
                    Route::get('', 'index')->name('admin.newsletters.contact-filters.index');
                    Route::post('', 'store')->name('admin.newsletters.contact-filters.store');
                    Route::post('count', 'countContacts')->name('admin.newsletters.contact-filters.count');
                    Route::put('{id}', 'update')->name('admin.newsletters.contact-filters.update');
                    Route::delete('{id}', 'destroy')->name('admin.newsletters.contact-filters.destroy');
                    Route::get('field-values', 'getFieldValues')->name('admin.newsletters.contact-filters.field-values');
                    Route::get('{id}/apply', 'applyFilter')->name('admin.newsletters.contact-filters.apply');
                });
            });
            Route::middleware('newsletters.permission:newsletters.contact-groups.delete')->group(function () {
                Route::delete('{id}', 'destroy')->name('admin.newsletters.contact-groups.destroy');
            });
            Route::get('{groupId}/contacts', 'contacts')->name('admin.newsletters.contact-groups.contacts');
            Route::middleware('newsletters.permission:newsletters.contact-groups.import')->group(function () {
                Route::post('{groupId}/import-mapping', 'saveImportMapping')->name('admin.newsletters.contact-groups.import-mapping');
                Route::post('{groupId}/import', 'importContacts')->name('admin.newsletters.contact-groups.import');
                Route::post('{groupId}/external-import', 'externalImport')->name('admin.newsletters.contact-groups.external-import');
            });
        });
    });

    /**
     * Contacts routes.
     */
    Route::middleware('newsletters.permission:newsletters.contacts.view')->group(function () {
        Route::controller(ContactController::class)->prefix('contacts')->group(function () {
            Route::get('', 'index')->name('admin.newsletters.contacts.index');
            Route::get('get', 'getContacts')->name('admin.newsletters.contacts.get');
            Route::middleware('newsletters.permission:newsletters.contacts.delete')->group(function () {
                Route::delete('clear-group', 'clearGroupContacts')->name('admin.newsletters.contacts.clear-group');
            });
        });
    });

    /**
     * Reports routes.
     */
    Route::middleware('newsletters.permission:newsletters.reports.view')->group(function () {
        Route::controller(ReportsController::class)->prefix('reports')->group(function () {
            Route::get('', 'index')->name('admin.newsletters.reports.index');
            Route::get('stats', 'stats')->name('admin.newsletters.reports.stats');
        });
    });

    /**
     * Account routes (for company owners).
     */
    Route::middleware('newsletters.permission:newsletters.account.view')->group(function () {
        Route::controller(AccountController::class)->prefix('account')->group(function () {
            Route::get('', 'index')->name('admin.newsletters.account.index');
            Route::middleware('newsletters.permission:newsletters.account.topup')->group(function () {
                Route::post('topup', 'topup')->name('admin.newsletters.account.topup');
            });
        });
    });

    /**
     * Admin Accounts routes (for super admins only).
     */
    Route::controller(AdminAccountController::class)->prefix('admin-accounts')->group(function () {
        Route::get('', 'index')->name('admin.newsletters.admin-accounts.index');
        Route::post('topup/{companyId}', 'topup')->name('admin.newsletters.admin-accounts.topup');
    });

    /**
     * Stop impersonation route (available for all authenticated users).
     */
    Route::post('stop-impersonate', [OwnersController::class, 'stopImpersonate'])->name('admin.newsletters.stop-impersonate');

});
