<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class ImageController extends Controller
{
    /**
     * Display the specified image.
     *
     * @param  string  $path
     * @return \Illuminate\Http\Response
     */
    public function show($path)
    {
        // Construct the full path
        $fullPath = 'avatars/' . $path;
        
        // Check if file exists
        if (!Storage::disk('public')->exists($fullPath)) {
            abort(404, 'Image not found');
        }

        // Get the file
        $file = Storage::disk('public')->get($fullPath);
        $mimeType = Storage::disk('public')->mimeType($fullPath);

        // Return the image with proper headers
        return Response::make($file, 200, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000',
            'Access-Control-Allow-Origin' => '*', // Or specify your frontend URL
        ]);
    }

    /**
     * Display any storage file.
     *
     * @param  string  $path
     * @return \Illuminate\Http\Response
     */
    public function showStorage($path)
    {
        // Check if file exists
        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'File not found');
        }

        // Get the file
        $file = Storage::disk('public')->get($path);
        $mimeType = Storage::disk('public')->mimeType($path);

        // Return the file with proper headers
        return Response::make($file, 200, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }
}