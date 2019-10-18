<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Routing\Controller;

class MigrateController extends Controller
{
    public function migrate(Request $request, string $openlistId)
    {
        // The "legacy-" prefix protects against high-jacking from another GUID.
        DB::table('searches')
            ->where(['guid' => 'legacy-' . $openlistId])
            ->update(['guid' => $request->user()->getId()]);
        // Always return success.
        return new Response('', 204);
    }
}
