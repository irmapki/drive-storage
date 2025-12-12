<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    /**
     * Isi kolom yang boleh diisi mass-assignment
     */
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

    /**
     * Casting kolom
     */
    protected $casts = [
        'is_favorite' => 'boolean',
    ];

    /**
     * Relasi ke User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke Favorite (jika pakai tabel favorites)
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Cek apakah file disukai user saat ini
     */
    public function isFavorite()
    {
        return $this->favorites()
            ->where('user_id', auth()->id())
            ->exists();
    }

    /**
     * Format ukuran file
     */
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

    /**
     * Icon berdasarkan tipe file
     */
    public function getFileIconAttribute()
    {
        $imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
        $documentTypes = ['pdf', 'doc', 'docx', 'txt'];
        $spreadsheetTypes = ['xls', 'xlsx', 'csv'];
        $archiveTypes = ['zip', 'rar', '7z', 'tar'];

        $type = strtolower($this->file_type);

        if (in_array($type, $imageTypes)) {
            return 'image';
        } elseif (in_array($type, $documentTypes)) {
            return 'document';
        } elseif (in_array($type, $spreadsheetTypes)) {
            return 'spreadsheet';
        } elseif (in_array($type, $archiveTypes)) {
            return 'archive';
        }

        return 'file';
    }
}
