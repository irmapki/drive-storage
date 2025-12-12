<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function index()
    {
        $favorites = Auth::user()->favorites()
            ->with('file')
            ->latest()
            ->paginate(12);
        
        return view('user.favorites.index', compact('favorites'));
    }
    
    public function toggle($fileId)
    {
        $file = File::where('user_id', Auth::id())->findOrFail($fileId);
        
        $favorite = Favorite::where([
            'user_id' => Auth::id(),
            'file_id' => $fileId
        ])->first();
        
        if ($favorite) {
            $favorite->delete();
            $message = 'File dihapus dari favorit!';
        } else {
            Favorite::create([
                'user_id' => Auth::id(),
                'file_id' => $fileId
            ]);
            $message = 'File ditambahkan ke favorit!';
        }
        
        return redirect()->back()->with('success', $message);
    }
}