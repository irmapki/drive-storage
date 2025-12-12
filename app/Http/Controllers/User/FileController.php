<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    // Dashboard - My Files
    public function index()
    {
        $files = auth()->user()->files()->latest()->get();
        $totalFiles = $files->count();
        $totalSize = $files->sum('file_size');
        
        return view('user.files.index', compact('files', 'totalFiles', 'totalSize'));
    }

    // Upload Form
    public function create()
    {
        return view('user.files.upload');
    }

    // Store Uploaded File
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:51200', // Max 50MB
        ]);

        if ($request->hasFile('file')) {
            $uploadedFile = $request->file('file');
            
            // Generate unique filename
            $originalName = $uploadedFile->getClientOriginalName();
            $extension = $uploadedFile->getClientOriginalExtension();
            $storedName = Str::uuid() . '.' . $extension;
            
            // Store file
            $path = $uploadedFile->storeAs('uploads', $storedName, 'public');
            
            // Save to database
            File::create([
                'user_id' => auth()->id(),
                'original_name' => $originalName,
                'stored_name' => $storedName,
                'file_type' => $extension,
                'mime_type' => $uploadedFile->getMimeType(),
                'file_size' => $uploadedFile->getSize(),
                'file_path' => $path,
            ]);

            return redirect()->route('user.files.index')
                ->with('success', 'File uploaded successfully!');
        }

        return back()->with('error', 'No file uploaded!');
    }

    // Download File
    public function download(File $file)
    {
        // Check if user owns the file
        if ($file->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        return Storage::disk('public')->download($file->file_path, $file->original_name);
    }

    // Delete File
    public function destroy(File $file)
    {
        // Check if user owns the file
        if ($file->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        // Delete from storage
        Storage::disk('public')->delete($file->file_path);
        
        // Delete from database
        $file->delete();

        return redirect()->route('user.files.index')
            ->with('success', 'File deleted successfully!');
    }

    // Toggle Favorite
    public function toggleFavorite(File $file)
    {
        if ($file->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $file->update([
            'is_favorite' => !$file->is_favorite
        ]);

        return back()->with('success', 'Favorite status updated!');
    }

    // Favorites Page
    public function favorites()
    {
        $files = auth()->user()->files()->where('is_favorite', true)->latest()->get();
        
        return view('user.files.favorites', compact('files'));
    }
}