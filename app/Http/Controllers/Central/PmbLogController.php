<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\PmbImportLog;
use Illuminate\Contracts\View\View;

class PmbLogController extends Controller
{
    public function index(): View
    {
        $logs = PmbImportLog::query()
            ->withoutGlobalScope('kampus')
            ->with(['kampus:id,nama,slug'])
            ->latest()
            ->paginate(20);

        return view('central.pmb-logs.index', compact('logs'));
    }
}
