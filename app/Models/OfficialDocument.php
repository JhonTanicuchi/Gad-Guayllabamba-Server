<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficialDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject',
        'description',
        'status',
        'created_by',
        'archived',
        'archived_at',
        'archived_by',
    ];

    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function archived_by()
    {
        return $this->belongsTo(User::class,'archived_by');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'official_document');
    }

    public function files()
    {
        return $this->hasMany(File::class, 'official_document');
    }

    public function recipients()
    {
        return $this->belongsToMany(User::class, 'official_document_has_recipients', 'official_document', 'users');
    }
}
