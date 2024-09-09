<?php

namespace App\Models;

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

    protected function calendars(): HasMany
    {
        return $this->hasMany(Calendar::class);
    }
}
