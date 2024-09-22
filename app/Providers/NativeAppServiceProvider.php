<?php

namespace App\Providers;

use Native\Laravel\Contracts\ProvidesPhpIni;
use Native\Laravel\Facades\MenuBar;
use Native\Laravel\Facades\Window;
use Native\Laravel\Menu\Menu;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        MenuBar::create()
            ->withContextMenu(
                Menu::new()
                    ->label('My Application')
                    ->separator()
                    ->link('https://nativephp.com', 'Learn more…')
                    ->separator()
                    ->quit()
            );

        Menu::new()
            ->fileMenu()
            ->editMenu()
            ->viewMenu()
            ->submenu('Tasks', Menu::new()
                ->checkbox('Show completed')
            )
            ->submenu('Help', Menu::new()
                ->link(route('main'), 'Open in browser')
                ->link(route('main'), 'Show app folder')
            )
            ->register();

        Window::open()
            ->width(800)
            ->height(1000)
            ->showDevTools(false);
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [];
    }
}
