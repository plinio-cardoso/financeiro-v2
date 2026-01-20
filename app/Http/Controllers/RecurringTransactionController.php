<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class RecurringTransactionController extends Controller
{
    /**
     * Display a listing of the recurring transactions
     */
    public function index(): View
    {
        return view('recurring-transactions.index');
    }
}
