<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PmbImportLog;
use Illuminate\Contracts\View\View;

class PmbLogController extends Controller
{
    public function index(): View
    {
        $logs = PmbImportLog::query()
            ->latest()
            ->paginate(20);

        return view('admin.pmb-logs.index', compact('logs'));
    }
}
