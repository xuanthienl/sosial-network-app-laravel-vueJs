<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Music;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MusicController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $songs = Music::get();
        return response()->json($songs, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if($request->song == '' || !preg_match('/^data:audio\/(\w+);base64,/', $request->song)) {
            return response()->json([
                'message' => 'upload failed!',
            ], 404);
        }
        $song = new Music;
        $song->authors = $request->authors;
        $song->title = $request->title;
        $song->artists = $request->artists;

        $extension = explode('/', mime_content_type($request->song))[1];
        $fileName = time().'.'. $extension;
        $path = 'public/songs/';
        Storage::disk('local')->put($path.$fileName, base64_decode(preg_replace('/^data:image\/(\w+);base64,/', '' ,$request->song)));
        $song->song = $fileName;
        $song->save();
        return response()->json($song, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Music  $music
     * @return \Illuminate\Http\Response
     */
    public function show(Music $music)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Music  $music
     * @return \Illuminate\Http\Response
     */
    public function edit(Music $music)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Music  $music
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Music $music)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Music  $music
     * @return \Illuminate\Http\Response
     */
    public function destroy(Music $music, Request $request)
    {
        $song = Music::find($request->id);
        if($song) {
            $path = 'public/songs/';
            if(Storage::disk('local')->exists($path.$song->song)) {
                Storage::disk('local')->delete($path.$song->song);
            }
            $song->forceDelete();
            return response()->json(['message' => 'success'], 200);
        }
        return response()->json(['message' => 'Not Found'], 404);
    }

    public function convertBase64(Request $request) {
        $path = 'public/songs/';
        if(Storage::disk('local')->exists($path.$request->file)) {
            $audio = base64_encode(Storage::disk('local')->get($path.$request->file));
            $base64 = 'data:audio/'.pathinfo($request->file)['extension'].';base64,'.$audio;
            return response()->json($base64, 200);
        } else {
            return response()->json(['message' => 'Not Found'], 404);
        }
    }
}
