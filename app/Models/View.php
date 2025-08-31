<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class View extends Model
{
    use SoftDeletes;

    protected $fillable = ['viewable_id', 'viewable_type', 'title', 'url', 'session_id', 'user_id', 'ip', 'agent'];

    public static function createViewLog($targetModel, $viewable, $userIp)
    {
        $views = new View();
        $views->viewable_type = $targetModel;
        $views->viewable_id = $viewable->id;
        $views->slug = $viewable->slug;
        $views->url = request()->url();
        $views->session_id = request()->getSession()->getId();
        $views->user_id = \Auth::check() ? \Auth::user()->id : null;
        $views->ip = $userIp;
        $views->agent = request()->header('User-Agent');
        $views->save();
    }
}
