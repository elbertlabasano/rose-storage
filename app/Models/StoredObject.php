<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class StoredObject extends Model
{
    public $incrementing = false;
    protected $table = 'objects';
    protected $keyType = 'string';
    protected $fillable = [
        'id', 'bucket_id', 'user_id', 'key', 'original_name', 'mime_type',
        'size', 'checksum', 'disk', 'visibility', 'metadata',
    ];

    protected $casts = ['metadata' => 'array'];

    protected static function booted(): void
    {
        static::creating(fn (StoredObject $object) => $object->id ??= (string) Str::uuid());
    }

    public function bucket()
    {
        return $this->belongsTo(Bucket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}


