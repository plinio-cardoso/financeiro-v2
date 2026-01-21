<?php

namespace Database\Seeders;

use App\Enums\TransactionStatusEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\Tag;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $tags = Tag::all();

        foreach ($users as $user) {
            $this->createTransactionsForUser($user, $tags);
        }
    }

    private function createTransactionsForUser(User $user, $tags): void
    {
        // 5 Débitos Pagos (mês anterior)
        $paidDebits = [
            ['title' => 'Supermercado', 'amount' => 350.00, 'tag_names' => ['Alimentação']],
            ['title' => 'Aluguel', 'amount' => 1500.00, 'tag_names' => ['Moradia']],
            ['title' => 'Plano de Saúde', 'amount' => 450.00, 'tag_names' => ['Saúde']],
            ['title' => 'Netflix', 'amount' => 39.90, 'tag_names' => ['Lazer']],
            ['title' => 'Gasolina', 'amount' => 250.00, 'tag_names' => ['Transporte']],
        ];

        foreach ($paidDebits as $index => $debit) {
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'title' => $debit['title'],
                'amount' => $debit['amount'],
                'type' => TransactionTypeEnum::Debit,
                'status' => TransactionStatusEnum::Paid,
                'due_date' => now()->subDays(15 + $index * 5),
                'paid_at' => now()->subDays(15 + $index * 5),
            ]);

            $this->attachTags($transaction, $tags, $debit['tag_names']);
        }

        // 3 Débitos Pendentes (vencimento futuro)
        $pendingDebits = [
            ['title' => 'Curso Online', 'amount' => 197.00, 'tag_names' => ['Educação'], 'days' => 10],
            ['title' => 'Farmácia', 'amount' => 120.00, 'tag_names' => ['Saúde'], 'days' => 15],
            ['title' => 'Cinema', 'amount' => 80.00, 'tag_names' => ['Lazer'], 'days' => 20],
        ];

        foreach ($pendingDebits as $debit) {
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'title' => $debit['title'],
                'amount' => $debit['amount'],
                'type' => TransactionTypeEnum::Debit,
                'status' => TransactionStatusEnum::Pending,
                'due_date' => now()->addDays($debit['days']),
                'paid_at' => null,
            ]);

            $this->attachTags($transaction, $tags, $debit['tag_names']);
        }

        // 2 Débitos Vencendo Hoje
        $dueTodayDebits = [
            ['title' => 'Conta de Luz', 'amount' => 180.00, 'tag_names' => ['Moradia']],
            ['title' => 'Internet', 'amount' => 99.90, 'tag_names' => ['Moradia']],
        ];

        foreach ($dueTodayDebits as $debit) {
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'title' => $debit['title'],
                'amount' => $debit['amount'],
                'type' => TransactionTypeEnum::Debit,
                'status' => TransactionStatusEnum::Pending,
                'due_date' => now(),
                'paid_at' => null,
            ]);

            $this->attachTags($transaction, $tags, $debit['tag_names']);
        }

        // 2 Débitos Vencidos (1-5 dias atrás)
        $overdueDebits = [
            ['title' => 'Uber', 'amount' => 45.00, 'tag_names' => ['Transporte'], 'days' => 2],
            ['title' => 'Restaurante', 'amount' => 150.00, 'tag_names' => ['Alimentação', 'Lazer'], 'days' => 5],
        ];

        foreach ($overdueDebits as $debit) {
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'title' => $debit['title'],
                'amount' => $debit['amount'],
                'type' => TransactionTypeEnum::Debit,
                'status' => TransactionStatusEnum::Pending,
                'due_date' => now()->subDays($debit['days']),
                'paid_at' => null,
            ]);

            $this->attachTags($transaction, $tags, $debit['tag_names']);
        }

        // 2 Créditos (Receitas) Pagos
        $paidCredits = [
            ['title' => 'Salário Empresa X', 'amount' => 5500.00, 'tag_names' => ['Salário']],
            ['title' => 'Rendimento Investimento', 'amount' => 350.00, 'tag_names' => ['Investimento']],
        ];

        foreach ($paidCredits as $index => $credit) {
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'title' => $credit['title'],
                'amount' => $credit['amount'],
                'type' => TransactionTypeEnum::Credit,
                'status' => TransactionStatusEnum::Paid,
                'due_date' => now()->subDays(5 + $index * 3),
                'paid_at' => now()->subDays(5 + $index * 3),
            ]);

            $this->attachTags($transaction, $tags, $credit['tag_names']);
        }

        // 1 Crédito Pendente
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'title' => 'Projeto Freelance',
            'amount' => 2000.00,
            'type' => TransactionTypeEnum::Credit,
            'status' => TransactionStatusEnum::Pending,
            'due_date' => now()->addDays(7),
            'paid_at' => null,
        ]);

        $this->attachTags($transaction, $tags, ['Freelance']);
    }

    private function attachTags(Transaction $transaction, $tags, array $tagNames): void
    {
        $tagIds = $tags->whereIn('name', $tagNames)->pluck('id');
        $transaction->tags()->attach($tagIds);
    }
}
