<?php

namespace LaveroAdmin\Admin\Notifications;

use Illuminate\Routing\Router;
use LaveroAdmin\Admin\Admin;
use LaveroAdmin\Admin\Extension;

class Notifications extends Extension
{
    public static function boot(): void
    {
        parent::routes(function (Router $router) {
            $router->post('notifications/channels/{id}/test', 'LaveroAdmin\Admin\Notifications\Controllers\ChannelController@test')->name('notifications.test');
            $router->resource('notifications/channels', 'LaveroAdmin\Admin\Notifications\Controllers\ChannelController', ['as' => 'notifications']);
            $router->resource('notifications/logs', 'LaveroAdmin\Admin\Notifications\Controllers\LogController', ['as' => 'notifications']);
        });

        Admin::extend('notifications', __CLASS__);
    }

    public static function import(): void
    {
        parent::createMenu('Notifications', 'notifications', 'icon-bell', 0, [
            ['title' => 'Channels', 'path' => 'notifications/channels', 'uri' => 'notifications/channels', 'icon' => 'icon-sliders-h'],
            ['title' => 'Logs', 'path' => 'notifications/logs', 'uri' => 'notifications/logs', 'icon' => 'icon-history'],
        ]);
        parent::createPermission('Notifications', 'ext.notifications', 'notifications*');
    }
}
