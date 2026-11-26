<?php

namespace LaveroAdmin\Admin\Notifications\Controllers;

use LaveroAdmin\Admin\Controllers\AdminController;
use LaveroAdmin\Admin\Grid;
use LaveroAdmin\Admin\Notifications\Models\NotificationLog;
use LaveroAdmin\Admin\Show;

class LogController extends AdminController
{
    protected function title()
    {
        return trans('lavero-admin-notifications::notifications.logs');
    }

    protected function grid(): Grid
    {
        $grid = new Grid(new NotificationLog());
        $grid->model()->orderBy('id', 'desc');
        $grid->disableCreateButton();
        $grid->actions(fn ($a) => $a->disableEdit());

        $grid->column('id', __('admin.id'))->sortable();
        $grid->column('event', trans('lavero-admin-notifications::notifications.events'))->label('primary');
        $grid->column('title', trans('admin.title'));
        $grid->column('status', 'Status')->label(['sent' => 'success', 'failed' => 'danger']);
        $grid->column('created_at');

        return $grid;
    }

    protected function detail($id): Show
    {
        $show = new Show(NotificationLog::findOrFail($id));
        $show->field('id');
        $show->field('event');
        $show->field('title');
        $show->field('message');
        $show->field('status');
        $show->field('error');
        $show->field('created_at');

        return $show;
    }
}
