<?php

namespace App\Http\Controllers\Artist;

use App\Artist;
use Carbon\Carbon;
use App\TypeArtist;
use App\Models\Follower;
use Illuminate\View\View;
use App\Models\Artist\Song;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Fomvasss\Youtube\Facades\Youtube;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ArtistController extends Controller
{

    public function __construct() 
    {
        $this->middleware('auth:artist')->except(['profile','oneSong','follow']);
    }

    public function index() : View
    {
        $artist = auth()->user();
        $typeArtists = null;
        if($artist->typeArtist->libele === 'Alternative' || $artist->typeArtist->id === 1){
            $typeArtists = TypeArtist::all();
        }
        $songs = $artist->songs()->orderBy('created_at', 'desc')->paginate(10);
        $videos = [
            Youtube::iFrame('https://www.youtube.com/watch?v=tEnCoocmPQM', [
                'rel'=> 0, 'controls'=>1, 'showinfo'=>1, 'frameborder'=>0
            ]),
            Youtube::iFrame('https://www.youtube.com/watch?v=tEnCoocmPQM', [
                'rel'=> 0, 'controls'=>1, 'showinfo'=>1, 'frameborder'=>0
            ]),
        ];
        
        return view('artist.index', compact('songs','artist','videos','typeArtists'));
    }

    public function setting ()
    {
        return view('artist.setting');
    }

    public function opportinuite() 
    {
        return view('opp');
    }

    public function profile(Artist $artist)
    {
        $videos = [
            Youtube::iFrame('https://www.youtube.com/watch?v=tEnCoocmPQM', [
                'rel'=> 0, 'controls'=>1, 'showinfo'=>1, 'frameborder'=>0
            ]),
            Youtube::iFrame('https://www.youtube.com/watch?v=tEnCoocmPQM', [
                'rel'=> 0, 'controls'=>1, 'showinfo'=>1, 'frameborder'=>0
            ]),
        ];
        $songs = $artist->songs()->orderBy('created_at','desc')->paginate(5);
        return view('artist.profile', compact('artist','songs','videos'));
    }

    public function oneSong(Artist $artist, Song $song) : View
    {
        // Afficher un song de l'artist concerné
        $lastSongs = $artist->songs()->orderBy('created_at','desc')->limit(5)->get();
        // dd($lastSongs->get());
        return view('artist.song', compact('artist','song','lastSongs'));
    }

    public function follow(Artist $artist)
    {
        if(Auth::user() == null && Auth::guard('artist')->user() == null){
            return response()->json(['code'=>403,'message'=>'Unauthorized'],403);
        }
        if(Auth::user() == null){
            $user = Auth::guard('artist')->user();
        }else{
            $user = Auth::user();
        }
        $className = get_class($artist);

        $subFollow = [
            'followable_type' => get_class($user),
            'followable_id' => $user->id,
            'artist_id' => $artist->id,
        ];
        if($artist->isFollowBy($user)){
            $follower = Follower::where($subFollow);
            $follower->delete();
            $nbfollow = $className::find($artist->id)->followers->count();
            return $this->jsonPrepare(200,'Follower Bien Supprimer',$nbfollow);
        }
        Follower::create($subFollow);
        $nbfollow = $className::find($artist->id)->followers->count();
        return $this->jsonPrepare(200,'Follower Bien ajouter',$nbfollow);
    }

    public function jsonPrepare(Int $code, String $message,Int $followers) 
    {
        return response()->json([
            'code' => $code,
            'message' => $message,
            'followers' => $followers,
        ],$code);
    }
}
