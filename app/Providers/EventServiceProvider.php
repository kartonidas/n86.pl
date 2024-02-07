<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;

use App\Listeners\LogUserSentMessage;
use App\Observers\BalanceDocumentObserver;
use App\Observers\CustomerObserver;
use App\Observers\CustomerInvoiceObserver;
use App\Observers\FileObserver;
use App\Observers\FirmObserver;
use App\Observers\ItemObserver;
use App\Observers\ItemBillObserver;
use App\Observers\ItemCyclicalFeeObserver;
use App\Observers\RentalObserver;
use App\Observers\UserInvitationObserver;
use App\Models\BalanceDocument;
use App\Models\Customer;
use App\Models\CustomerInvoice;
use App\Models\File;
use App\Models\Firm;
use App\Models\Item;
use App\Models\ItemBill;
use App\Models\ItemCyclicalFee;
use App\Models\Rental;
use App\Models\UserInvitation;


class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        
        MessageSent::class => [
            LogUserSentMessage::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        UserInvitation::observe(UserInvitationObserver::class);
        File::observe(FileObserver::class);
        Firm::observe(FirmObserver::class);
        Rental::observe(RentalObserver::class);
        Item::observe(ItemObserver::class);
        ItemBill::observe(ItemBillObserver::class);
        ItemCyclicalFee::observe(ItemCyclicalFeeObserver::class);
        BalanceDocument::observe(BalanceDocumentObserver::class);
        Customer::observe(CustomerObserver::class);
        CustomerInvoice::observe(CustomerInvoiceObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
