<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'filename',
        'original_filename',
        'file_path',
        'file_size',
        'file_type',
        'title',
        'description',
        'category',
        'version',
        'documentable_id',
        'documentable_type',
        'uploaded_by',
    ];

    protected $appends = [
        'download_url',
    ];

    // Accessors
    public function getDownloadUrlAttribute()
    {
        return route('documents.download', $this->id);
    }

    public function getFileSizeForHumansAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    // Relationships
    public function documentable()
    {
        return $this->morphTo();
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Scopes
    public function scopeInCategory($query, $category)
    {
        if (empty($category)) {
            return $query;
        }

        return $query->where('category', $category);
    }

    public function scopeOfType($query, $fileType)
    {
        if (empty($fileType)) {
            return $query;
        }

        return $query->where('file_type', 'like', "%{$fileType}%");
    }

    public function scopeSearch($query, $search)
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('original_filename', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    // Methods
    public function getDownloadPath()
    {
        return Storage::path($this->file_path);
    }
}