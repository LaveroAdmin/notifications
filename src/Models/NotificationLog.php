<?php

namespace LaveroAdmin\Admin\Notifications\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    protected $fillable = ['channel_id', 'event', 'title', 'message', 'status', 'error'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setConnection(config('admin.database.connection') ?: config('database.default'));
    }
}
