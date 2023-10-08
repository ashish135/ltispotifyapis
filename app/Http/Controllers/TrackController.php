<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Track;

class TrackController extends Controller
{
    public function create()
    {
        return view('tracks.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'isrc' => 'required|string',
        ]);

        $isrc = $request->input('isrc');
        
        // Check if the track already exists in the database
        if (Track::where('isrc', $isrc)->exists()) {
            return redirect()->route('tracks.create')->with('message', 'Track already exists');
        }

        // Fetch track details from the Spotify API
        $response = Http::withHeaders([
            'Authorization' => 'Bearer BQA6HXU-0GGl2aNG60eMTwOow0nriwBN5PoiPtWfb-9Quk1tRcWsOwxpDtQM2AHmLQjD2FT9HuzVckIrDjues4Ix_TyYMNiTtJjxvsKRKLnK8ZUA85iID1GodZ0-7bcF4xZIgfkBBAA94MHy2Drj2CTLj7-RyTIsUXxshvPHdOPsTcq_W2dLZKFcZiWC6-0KgYEo77-vbgEoJHa0gtPuhaYXAyfMMRfqv6Rrz_fcB0QBAK1BNpcCrtSLC3ccEWUbruWe7ri3TnaqWQ60',
        ])->get('https://api.spotify.com/v1/search?q=isrc:'.$isrc.'&type=track');

        if ($response->successful()) {
            $trackData = $response->json();
            // Extract metadata from the Spotify API response
            if($trackData['tracks'] && $trackData['tracks']['items']){
                foreach($trackData['tracks']['items'] as $item){
                    $spotifyImageUri = $item['album']['images'][0]['url'];
                    $title = $item['name'];
                    $artists = collect($item['artists'])->pluck('name')->implode(', ');
                    // Store the track in the database
                    try {
                        // Attempt to create the track
                        Track::create([
                            'isrc' => $isrc,
                            'spotify_image_uri' => $spotifyImageUri,
                            'title' => $title,
                            'artist_name_list' => $artists,
                        ]);
                
                        return redirect()->route('welcome')->with('message', $title.' Track created successfully');
                    } catch (\Illuminate\Database\QueryException $e) {
                        // Handle the duplicate entry error
                        if ($e->errorInfo[1] === 1062) {
                            // Duplicate entry error (SQLSTATE[23000])
                            return redirect()->route('tracks.create')->with('error', 'Track with this ISRC already exists');
                        }
                
                        // Handle other database errors if needed
                        return redirect()->route('tracks.create')->with('error', 'Failed to create the track');
                    }
                }
            }
            return redirect()->route('welcome')->with('message', 'Track created successfully');
        }

        return redirect()->route('tracks.create')->with('error', 'Failed to fetch data from Spotify API');
    }
    
    public function findByISRC(Request $request)
    {
        $request->validate([
            'isrc' => 'required|string',
        ]);

        $isrc = $request->input('isrc');

        // Search for the track by ISRC in the database
        $track = Track::where('isrc', $isrc)->first();

        if (!$track) {
            return redirect()->route('tracks.create')->with('error', 'Track not found');
        }

        return view('tracks.show', ['track' => $track]);
    }
}

