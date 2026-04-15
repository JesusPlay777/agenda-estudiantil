<?php

namespace App\Providers;

use App\Events\AssignmentPublished;
use App\Events\AssignmentReviewed;
use App\Listeners\SendAssignmentPublishedNotification;
use App\Listeners\SendAssignmentReviewedNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(AssignmentPublished::class, SendAssignmentPublishedNotification::class);
        Event::listen(AssignmentReviewed::class, SendAssignmentReviewedNotification::class);
    }
}
