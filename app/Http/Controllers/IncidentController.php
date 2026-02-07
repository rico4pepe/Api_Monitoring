<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incident;

class IncidentController extends Controller
{
    //
      public function index()
    {
        $openIncidents = Incident::where('status', 'open')
            ->orderByDesc('started_at')
            ->get();

        $resolvedIncidents = Incident::where('status', 'resolved')
            ->orderByDesc('resolved_at')
            ->limit(20)
            ->get();

        return view('incidents.index', compact(
            'openIncidents',
            'resolvedIncidents'
        ));
    }
}
