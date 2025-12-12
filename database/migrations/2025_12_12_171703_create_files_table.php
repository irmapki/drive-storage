<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('original_name'); // Nama file asli
            $table->string('stored_name'); // Nama file di storage
            $table->string('file_type'); // extension (pdf, jpg, dll)
            $table->string('mime_type'); // MIME type
            $table->bigInteger('file_size'); // Size dalam bytes
            $table->string('file_path'); // Path di storage
            $table->boolean('is_favorite')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};