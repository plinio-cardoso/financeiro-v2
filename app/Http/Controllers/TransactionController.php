<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Tag;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function __construct(
        private TransactionService $transactionService
    ) {}

    /**
     * Display a listing of the transactions
     */
    public function index(Request $request): View
    {
        return view('transactions.index');
    }

    /**
     * Show the form for creating a new transaction
     */
    public function create(): View
    {
        $tags = Tag::orderBy('name')->get();

        return view('transactions.create', compact('tags'));
    }

    /**
     * Store a newly created transaction
     */
    public function store(StoreTransactionRequest $request): RedirectResponse
    {
        $this->transactionService->createTransaction($request->validated());

        return redirect()->route('transactions.index')
            ->with('success', 'Transação criada com sucesso!');
    }

    /**
     * Display the specified transaction
     */
    public function show(Transaction $transaction): View
    {
        return view('transactions.show', compact('transaction'));
    }

    /**
     * Show the form for editing the transaction
     */
    public function edit(Transaction $transaction): View
    {
        $tags = Tag::orderBy('name')->get();

        return view('transactions.edit', compact('transaction', 'tags'));
    }

    /**
     * Update the specified transaction
     */
    public function update(UpdateTransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $this->transactionService->updateTransaction($transaction, $request->validated());

        return redirect()->route('transactions.index')
            ->with('success', 'Transação atualizada com sucesso!');
    }

    /**
     * Remove the specified transaction
     */
    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->transactionService->deleteTransaction($transaction);

        return redirect()->route('transactions.index')
            ->with('success', 'Transação excluída com sucesso!');
    }
}
