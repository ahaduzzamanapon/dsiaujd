<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PendingStream;
use App\Services\StreamDeduplicator;
use Illuminate\Http\Request;

class PendingStreamController extends Controller
{
    /**
     * Show the review queue page.
     */
    public function index()
    {
        $pending = PendingStream::latest()->paginate(20);
        $totalCount = PendingStream::count();
        return view('admin.review-queue', compact('pending', 'totalCount'));
    }

    /**
     * Approve a pending stream — move it to the live streams table.
     */
    public function approve(Request $request, int $id)
    {
        $pending = PendingStream::findOrFail($id);

        try {
            StreamDeduplicator::syncChannelWithDeduplication(
                $pending->name,
                $pending->logo,
                'Server',
                $pending->url,
                $pending->http_referer,
                $pending->http_origin,
                $pending->category ?? 'Live Channel'
            );

            $pending->delete();

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => "'{$pending->name}' approved and is now live."]);
            }
            return back()->with('success', "'{$pending->name}' approved and is now live.");
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Failed to approve: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Failed to approve: ' . $e->getMessage());
        }
    }

    /**
     * Reject a pending stream — delete it permanently.
     */
    public function reject(Request $request, int $id)
    {
        $pending = PendingStream::findOrFail($id);
        $name = $pending->name;
        $pending->delete();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => "'{$name}' rejected and removed."]);
        }
        return back()->with('success', "'{$name}' rejected and removed.");
    }

    /**
     * Reject all pending streams at once.
     */
    public function rejectAll(Request $request)
    {
        $count = PendingStream::count();
        PendingStream::truncate();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => "{$count} pending streams cleared."]);
        }
        return back()->with('success', "{$count} pending streams cleared.");
    }
}
