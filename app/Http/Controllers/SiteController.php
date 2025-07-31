<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function show($siteId)
    {
        $site = Site::findOrFail($siteId);
        return view('site.show', compact('site'));
    }
}
