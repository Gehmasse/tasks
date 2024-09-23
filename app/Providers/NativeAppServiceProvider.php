<?php

namespace App\Providers;

use Native\Laravel\Contracts\ProvidesPhpIni;
use Native\Laravel\Facades\Window;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        Window::open()
            ->width(800)
            ->height(1000)
            ->showDevTools(false)
            ->rememberState();
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [];
    }
}
