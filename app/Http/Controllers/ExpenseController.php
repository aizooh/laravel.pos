<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    public const CATEGORIES = [
        'Rent',
        'Electricity',
        'Water',
        'Internet',
        'Salary',
        'Transport',
        'Stationery',
        'Airtime',
        'Repairs',
        'Licenses',
        'Other',
    ];

    public function index(Request $request)
    {
        $q = Expense::with('user');

        if ($from = $request->input('from')) {
            $q->whereDate('date', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $q->whereDate('date', '<=', $to);
        }
        if ($cat = $request->input('category')) {
            $q->where('category', $cat);
        }

        $expenses = (clone $q)->latest('date')->latest('id')->paginate(20)->withQueryString();
        $total    = (clone $q)->sum('amount');
        $categories = self::CATEGORIES;

        return view('expenses.index', compact('expenses', 'total', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'description' => ['required', 'string', 'max:150'],
            'amount'      => ['required', 'numeric', 'min:0.01'],
            'category'    => ['required', Rule::in(self::CATEGORIES)],
            'date'        => ['required', 'date'],
        ]);

        $data['user_id'] = $request->user()->id;

        Expense::create($data);

        return redirect()->route('expenses.index')
            ->with('ok', 'Expense recorded.');
    }

    public function update(Request $request, Expense $expense)
    {
        $data = $request->validate([
            'description' => ['required', 'string', 'max:150'],
            'amount'      => ['required', 'numeric', 'min:0.01'],
            'category'    => ['required', Rule::in(self::CATEGORIES)],
            'date'        => ['required', 'date'],
        ]);

        $expense->update($data);

        return redirect()->route('expenses.index')
            ->with('ok', 'Expense updated.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('ok', 'Expense deleted.');
    }
}