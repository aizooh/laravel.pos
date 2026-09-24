<?php

namespace App\Http\Controllers;

use App\Models\DailyPosition;
use Illuminate\Http\Request;

class DailyPositionController extends Controller
{
    public function index(Request $request)
    {
        $today   = today();
        $opening = DailyPosition::where('date', $today)->opening()->with('user')->first();
        $closing = DailyPosition::where('date', $today)->closing()->with('user')->first();

        // History grouped by date (newest first)
        $history = DailyPosition::with('user')
            ->orderByDesc('date')
            ->orderBy('type') // opening before closing
            ->limit(120)
            ->get()
            ->groupBy(fn ($p) => $p->date->toDateString());

        return view('positions.index', compact('today', 'opening', 'closing', 'history'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $date = $data['date'] ?? today()->toDateString();

        $exists = DailyPosition::where('date', $date)
            ->where('type', $data['type'])
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'type' => ucfirst($data['type']) . ' position for ' . $date . ' already exists.',
            ])->withInput();
        }

        $data['date']    = $date;
        $data['user_id'] = $request->user()->id;
        $data['total']   = DailyPosition::sumFields($data);

        DailyPosition::create($data);

        return redirect()->route('positions.index')
            ->with('ok', ucfirst($data['type']) . ' position saved for ' . $date . '.');
    }

    public function update(Request $request, DailyPosition $position)
    {
        // Attendant can only edit their own, and only same-day
        if (! $request->user()->isAdmin()) {
            if ($position->user_id !== $request->user()->id) {
                abort(403, 'You can only edit positions you recorded.');
            }
            if (! $position->date->isToday()) {
                abort(403, 'You can only edit today\'s position.');
            }
        }

        $data = $this->validated($request);
        unset($data['type'], $data['date']); // cannot change type/date on edit

        $data['total'] = DailyPosition::sumFields([
            'kcb'    => $data['kcb']    ?? $position->kcb,
            'equity' => $data['equity'] ?? $position->equity,
            'absa'   => $data['absa']   ?? $position->absa,
            'mpesa'  => $data['mpesa']  ?? $position->mpesa,
            'cash'   => $data['cash']   ?? $position->cash,
            'other'  => $data['other']  ?? $position->other,
        ]);

        $position->update($data);

        return redirect()->route('positions.index')
            ->with('ok', 'Position updated.');
    }

    public function destroy(Request $request, DailyPosition $position)
    {
        if (! $request->user()->isAdmin()) {
            abort(403);
        }

        $position->delete();

        return redirect()->route('positions.index')->with('ok', 'Record deleted.');
    }

    // ----------------------------------------------------------------

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'type'        => 'required|in:opening,closing',
            'date'        => 'nullable|date|before_or_equal:today',
            'kcb'         => 'nullable|numeric|min:0',
            'equity'      => 'nullable|numeric|min:0',
            'absa'        => 'nullable|numeric|min:0',
            'mpesa'       => 'nullable|numeric|min:0',
            'cash'        => 'nullable|numeric|min:0',
            'other'       => 'nullable|numeric|min:0',
            'other_label' => 'nullable|string|max:60',
            'notes'       => 'nullable|string|max:200',
        ]);

        // Normalize nulls to 0 for the numeric fields
        foreach (['kcb','equity','absa','mpesa','cash','other'] as $f) {
            $data[$f] = $data[$f] ?? 0;
        }

        return $data;
    }
}