<?php

namespace LaveroAdmin\Admin\Notifications\Controllers;

use Illuminate\Http\RedirectResponse;
use LaveroAdmin\Admin\Controllers\AdminController;
use LaveroAdmin\Admin\Form;
use LaveroAdmin\Admin\Grid;
use LaveroAdmin\Admin\Notifications\Models\NotificationChannel;
use LaveroAdmin\Admin\Notifications\NotificationService;
use LaveroAdmin\Admin\Show;

class ChannelController extends AdminController
{
    protected function title()
    {
        return trans('lavero-admin-notifications::notifications.channels');
    }

    protected function grid(): Grid
    {
        $grid = new Grid(new NotificationChannel());
        $grid->column('id', __('admin.id'))->sortable();
        $grid->column('name', trans('lavero-admin-notifications::notifications.name'));
        $grid->column('driver', trans('lavero-admin-notifications::notifications.driver'))->label('info');
        $grid->column('is_active', trans('lavero-admin-notifications::notifications.is_active'))->switch();
        $grid->column('test', trans('lavero-admin-notifications::notifications.test'))->display(function () {
            $url = route('notifications.test', ['id' => $this->id]);
            $csrf = csrf_token();
            return "<form action='{$url}' method='POST' class='d-inline'><input type='hidden' name='_token' value='{$csrf}'><button type='submit' class='btn btn-xs btn-outline-primary'>Test</button></form>";
        });

        return $grid;
    }

    protected function detail($id): Show
    {
        $show = new Show(NotificationChannel::findOrFail($id));
        $show->field('id');
        $show->field('name');
        $show->field('driver');
        $show->field('is_active');
        $show->field('created_at');

        return $show;
    }

    protected function form(): Form
    {
        $form = new Form(new NotificationChannel());
        $form->text('name', trans('lavero-admin-notifications::notifications.name'))->rules('required');
        $form->select('driver', trans('lavero-admin-notifications::notifications.driver'))->options([
            'telegram' => 'Telegram Bot',
            'webhook'  => 'Webhook (Slack / Discord / Custom)',
            'email'    => 'Email',
        ])->default('telegram');
        $form->embeds('config', trans('lavero-admin-notifications::notifications.config'), function ($form) {
            $form->text('bot_token', 'Telegram Bot Token');
            $form->text('chat_id', 'Telegram Chat ID');
            $form->text('url', 'Webhook URL');
            $form->email('email', 'Email');
        });
        $form->multipleSelect('events', trans('lavero-admin-notifications::notifications.events'))->options([
            '*'              => 'All Events (*)',
            'wafu.block'     => 'WAFU Block',
            'reporter.error' => 'Exception Reporter',
            'backup.done'    => 'Backup',
            'queue.failed'   => 'Queue Failed',
            'form.lead'      => 'Form Submission',
        ]);
        $form->switch('is_active', trans('lavero-admin-notifications::notifications.is_active'))->default(1);

        return $form;
    }

    public function test($id): RedirectResponse
    {
        $channel = NotificationChannel::findOrFail($id);
        $ok = NotificationService::sendToChannel($channel->toArray(), 'test', 'LaveroAdmin Test', 'Notification channel is working!');
        admin_toastr($ok ? 'Test notification sent!' : 'Failed to send test notification.', $ok ? 'success' : 'error');

        return back();
    }
}
