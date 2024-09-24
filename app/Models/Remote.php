<?php

namespace App\Models;

use App\Client;
use App\Exceptions\ConnectionException;
use App\Exceptions\StatusCodeException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $name
 * @property string $href
 * @property string $username
 * @property string $password
 * @property-read Collection<int, Calendar> $calendars
 */
class Remote extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'href', 'username', 'password'];

    /**
     * Compares calendar list from remote and local, adds missing
     * calendars and updates their tasks if necessary.
     *
     * @throws ConnectionException
     * @throws StatusCodeException
     */
    public function sync(): int
    {
        $i = 0;

        $client = Client::new($this);

        foreach ($client->calendars() as $calendar) {
            $local = Calendar::query()->where('href', $calendar->href)->first();

            // create calendar if not exists
            if ($local === null) {
                $calendar->save();
                $client->updateCalendar($calendar, $calendar->ctag);
                $i++;

                continue;
            }

            // update calendar if ctag and so content has changed
            if ($calendar->ctag !== $local->ctag) {

                $client->updateCalendar($local, $calendar->ctag);
                $i++;
            }
        }

        return $i;
    }

    protected function calendars(): HasMany
    {
        return $this->hasMany(Calendar::class);
    }
}
