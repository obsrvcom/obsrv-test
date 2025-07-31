<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\Site;

class EnsureSiteAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $siteId = $request->route('site');
        $site = Site::find($siteId);

        // Defensive: If $site is a collection, use the first item
        if ($site instanceof \Illuminate\Database\Eloquent\Collection) {
            \Log::warning('Site is a collection in EnsureSiteAccess', ['siteId' => $siteId]);
            $site = $site->first();
        }
        \Log::info('EnsureSiteAccess site debug', ['site' => $site, 'site_type' => is_object($site) ? get_class($site) : gettype($site)]);

        if (!$site || !$user->canAccessSite($site)) {
            abort(403, 'You do not have access to this site.');
        }

        // Optionally, set current site in request/context
        $request->attributes->set('current_site', $site);

        return $next($request);
    }
}
