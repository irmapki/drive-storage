<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'original_name',
        'stored_name',
        'file_type',
        'mime_type',
        'file_size',
        'file_path',
        'is_favorite',
    ];

    protected $casts = [
        'is_favorite' => 'boolean',
    ];

    // Relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper untuk format ukuran file
    public function getFormattedSizeAttribute()
    {
        $size = $this->file_size;
        if ($size < 1024) {
            return $size . ' B';
        } elseif ($size < 1048576) {
            return round($size / 1024, 2) . ' KB';
        } elseif ($size < 1073741824) {
            return round($size / 1048576, 2) . ' MB';
        } else {
            return round($size / 1073741824, 2) . ' GB';
        }
    }

    // Helper untuk icon berdasarkan tipe file
    public function getFileIconAttribute()
    {
        $imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
        $documentTypes = ['pdf', 'doc', 'docx', 'txt'];
        $spreadsheetTypes = ['xls', 'xlsx', 'csv'];
        $archiveTypes = ['zip', 'rar', '7z', 'tar'];

        if (in_array(strtolower($this->file_type), $imageTypes)) {
            return 'image';
        } elseif (in_array(strtolower($this->file_type), $documentTypes)) {
            return 'document';
        } elseif (in_array(strtolower($this->file_type), $spreadsheetTypes)) {
            return 'spreadsheet';
        } elseif (in_array(strtolower($this->file_type), $archiveTypes)) {
            return 'archive';
        } else {
            return 'file';
        }
    }
}