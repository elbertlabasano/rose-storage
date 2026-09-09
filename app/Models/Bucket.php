<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Bucket extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['user_id', 'name'];
    protected static function booted(): void
    {
        static::creating(fn (Bucket $bucket) => $bucket->id ??= (string) Str::uuid());
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function objects()
    {
        return $this->hasMany(StoredObject::class);
    }
}


