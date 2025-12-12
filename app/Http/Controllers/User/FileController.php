<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    /**
     * Display a listing of user's files
     */
    public function index(Request $request)
    {
        $query = File::where('user_id', Auth::id())
            ->with('favorites');

        // Filter by type if provided
        if ($request->has('type') && in_array($request->type, ['image', 'document'])) {
            $query->where('plain_type', $request->type);
        }

        $files = $query->latest()->paginate(12);

        return view('user.files.index', compact('files'));
    }

    /**
     * Show the form for uploading a new file
     */
    public function create()
    {
        return view('user.files.create');
    }

    /**
     * Store a newly uploaded file
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'plain_type' => 'required|in:image,document',
        ]);

        $uploadedFile = $request->file('file');
        
        // Validate file type based on plain_type
        if ($request->plain_type === 'image') {
            $request->validate([
                'file' => 'mimes:jpg,jpeg,png,gif,webp'
            ]);
        } else {
            $request->validate([
                'file' => 'mimes:pdf,doc,docx,xls,xlsx,txt'
            ]);
        }

        // Store file
        $path = $uploadedFile->store('uploads/' . Auth::id(), 'public');

        // Create file record
        File::create([
            'user_id' => Auth::id(),
            'name' => $uploadedFile->getClientOriginalName(),
            'path' => $path,
            'size' => $uploadedFile->getSize(),
            'type' => $uploadedFile->getMimeType(),
            'plain_type' => $request->plain_type,
        ]);

        return redirect()->route('user.files.index')->with('success', 'File uploaded successfully!');
    }

    /**
     * Download a file
     */
    public function download(File $file)
    {
        // Ensure the file belongs to the authenticated user
        if ($file->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if (!Storage::disk('public')->exists($file->path)) {
            return redirect()->back()->with('error', 'File not found.');
        }

        return Storage::disk('public')->download($file->path, $file->name);
    }

    /**
     * Remove the specified file
     */
    public function destroy(File $file)
    {
        // Ensure the file belongs to the authenticated user
        if ($file->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Delete file from storage
        if (Storage::disk('public')->exists($file->path)) {
            Storage::disk('public')->delete($file->path);
        }

        // Delete favorites associated with this file
        $file->favorites()->delete();

        // Delete file record from database
        $file->delete();

        return redirect()->back()->with('success', 'File deleted successfully!');
    }

    /**
     * Toggle favorite status for a file
     */
    public function toggleFavorite(File $file)
    {
        // Ensure the file belongs to the authenticated user
        if ($file->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $favorite = Favorite::where([
            'user_id' => Auth::id(),
            'file_id' => $file->id
        ])->first();

        if ($favorite) {
            $favorite->delete();
            $message = 'File removed from favorites!';
        } else {
            Favorite::create([
                'user_id' => Auth::id(),
                'file_id' => $file->id
            ]);
            $message = 'File added to favorites!';
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Display user's favorite files
     */
    public function favorites()
    {
        $favorites = Favorite::where('user_id', Auth::id())
            ->with('file')
            ->latest()
            ->paginate(12);

        return view('user.files.favorites', compact('favorites'));
    }
}