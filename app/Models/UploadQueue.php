<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $task_id
 * @property Task $task
 */
class UploadQueue extends Model
{
    use HasFactory;

    protected $table = 'upload_queue';

    protected $fillable = ['task_id'];

    public static function add(Task $task): void
    {
        if (! self::query()->where('task_id', $task->id)->exists()) {
            self::create(['task_id' => $task->id]);
        }
    }

    public static function remove(Task $task): void
    {
        self::query()->where('task_id', $task->id)->delete();
    }

    public static function work(int $total): int
    {
        $i = 0;

        /** @var self $item */
        foreach (self::query()->limit($total)->get() as $item) {
            dump($item->toArray());
            $i++;
            //            $item->task->upload(); FIXME
            $item->delete();
        }

        return $i;
    }

    protected function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
