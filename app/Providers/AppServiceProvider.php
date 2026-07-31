<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Passport::tokensExpireIn(now()->addHours(2));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::authorizationView('auth.oauth.authorize');

        Passport::tokensCan([
            'courses.read' => 'Read courses',
            'courses.write' => 'Create/update/delete courses',
            'students.read' => 'Read students',
            'grades.read' => 'Read exam grades',
            'meetings.read' => 'Read Zoom/course meetings',
            'certificates.write' => 'Generate certificates',
        ]);

        Passport::setDefaultScope([
            'courses.read',
        ]);
    }
}
