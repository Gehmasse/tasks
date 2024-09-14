<?php

namespace App\Models;

use App\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $calendar_id
 * @property string $href
 */
class DownloadQueue extends Model
{
    use HasFactory;

    protected $table = 'download_queue';

    protected $fillable = ['calendar_id', 'href'];

    public static function add(string $calendarId, string $href): void
    {
        if (! self::query()->where('href', $href)->exists()) {
            self::create(['calendar_id' => $calendarId, 'href' => $href]);
        }
    }

    public static function remove(string $calendarId, string $href): void
    {
        self::query()
            ->where('calendar_id', $calendarId)
            ->where('href', $href)
            ->delete();
    }

    public static function work(int $total): int
    {
        $i = 0;

        foreach (Calendar::all() as $calendar) {
            $client = Client::new($calendar->remote);

            /** @var self $item */
            $items = self::query()->where('calendar_id', $calendar->id)->limit($total)->get();
            $i += $items->count();

            foreach ($client->tasks($calendar, hrefs: $items->pluck('href')->toArray()) as $task) {
                $task->createOrUpdate();
                self::remove($calendar->id, $task->href);
            }
        }

        return $i;
    }
}
