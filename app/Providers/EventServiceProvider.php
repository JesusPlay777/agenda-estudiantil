<?php

namespace App\Providers;

use App\Events\AssignmentPublished;
use App\Events\AssignmentReviewed;
use App\Listeners\SendAssignmentPublishedNotification;
use App\Listeners\SendAssignmentReviewedNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        AssignmentPublished::class => [
            SendAssignmentPublishedNotification::class,
        ],
        AssignmentReviewed::class => [
            SendAssignmentReviewedNotification::class,
        ],
    ];
}
