<?php

namespace App\Providers;

use App\DAO\ComplaintDAO;
use App\DAO\Interfaces\ComplaintDAOInterface;
use App\Models\Comment;
use App\Models\Complaint;
use App\Observers\CommentObserver;
use App\Observers\ComplaintObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ComplaintDAOInterface::class, ComplaintDAO::class);
    }

    public function boot(): void
    {
       Complaint::observe(ComplaintObserver::class);
        Comment::observe(CommentObserver::class);
    }
}
