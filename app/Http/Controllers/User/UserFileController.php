<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserFileController extends Controller
{
    public function index()
    {
        $files = File::where('user_id', Auth::id())
            ->with('favorites')
            ->latest()
            ->paginate(12);
        
        return view('user.files.index', compact('files'));
    }
    
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'plain_type' => 'required|in:image,document',
        ]);
        
        $file = $request->file('file');
        $path = $file->store('uploads/' . Auth::id(), 'public');
        
        File::create([
            'user_id' => Auth::id(),
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'size' => $file->getSize(),
            'type' => $file->getMimeType(),
            'plain_type' => $request->plain_type,
        ]);
        
        return redirect()->back()->with('success', 'File berhasil diupload!');
    }
    
    public function download($id)
    {
        $file = File::where('user_id', Auth::id())->findOrFail($id);
        
        return Storage::disk('public')->download($file->path, $file->name);
    }
    
    public function destroy($id)
    {
        $file = File::where('user_id', Auth::id())->findOrFail($id);
        
        // Delete file from storage
        Storage::disk('public')->delete($file->path);
        
        // Delete from database
        $file->delete();
        
        return redirect()->back()->with('success', 'File berhasil dihapus!');
    }
}