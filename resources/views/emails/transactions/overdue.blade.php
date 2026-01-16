<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contas Vencidas</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #c53030 0%, #9b2c2c 100%);
            color: white;
            padding: 30px;
            border-radius: 10px 10px 0 0;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .alert {
            background: #fed7d7;
            border-left: 4px solid #c53030;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            color: #742a2a;
        }
        .content {
            background: #f7fafc;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .transaction {
            background: white;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 8px;
            border-left: 4px solid #c53030;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .transaction-title {
            font-size: 18px;
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 10px;
        }
        .transaction-details {
            font-size: 14px;
            color: #718096;
        }
        .transaction-amount {
            font-size: 20px;
            font-weight: bold;
            color: #c53030;
            margin-top: 10px;
        }
        .overdue-badge {
            display: inline-block;
            background: #c53030;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 8px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            color: #718096;
            font-size: 14px;
        }
        .total-section {
            background: white;
            padding: 20px;
            margin-top: 20px;
            border-radius: 8px;
            text-align: center;
            border: 2px solid #c53030;
        }
        .total-amount {
            font-size: 32px;
            font-weight: bold;
            color: #c53030;
        }
        .action-button {
            display: inline-block;
            background: #c53030;
            color: white;
            padding: 12px 30px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🚨 Atenção: Contas Vencidas</h1>
        <p style="margin: 10px 0 0 0; opacity: 0.9;">{{ now()->format('d/m/Y') }}</p>
    </div>

    <div class="content">
        <div class="alert">
            <strong>⚠️ Importante:</strong> Você tem contas em atraso que precisam de atenção imediata.
        </div>

        <p>Olá,</p>
        <p>Você tem <strong>{{ count($transactions) }}</strong> {{ count($transactions) === 1 ? 'conta vencida' : 'contas vencidas' }}:</p>

        @foreach($transactions as $transaction)
        <div class="transaction">
            <div class="transaction-title">{{ $transaction->title }}</div>

            @if($transaction->description)
            <div class="transaction-details">{{ $transaction->description }}</div>
            @endif

            <div class="transaction-details">
                Vencimento: <strong>{{ $transaction->due_date->format('d/m/Y') }}</strong>
            </div>

            @php
                $daysOverdue = now()->diffInDays($transaction->due_date, false);
                $daysOverdueAbs = abs((int)$daysOverdue);
            @endphp

            <span class="overdue-badge">
                Vencida há {{ $daysOverdueAbs }} {{ $daysOverdueAbs === 1 ? 'dia' : 'dias' }}
            </span>

            <div class="transaction-amount">
                R$ {{ number_format($transaction->amount, 2, ',', '.') }}
            </div>
        </div>
        @endforeach

        @if(count($transactions) > 1)
        <div class="total-section">
            <div style="color: #718096; margin-bottom: 10px;">Total em Atraso</div>
            <div class="total-amount">
                R$ {{ number_format($transactions->sum('amount'), 2, ',', '.') }}
            </div>
        </div>
        @endif

        <p style="margin-top: 30px;">
            <strong>Recomendamos que você regularize essas pendências o quanto antes para evitar juros e multas.</strong>
        </p>

        <div style="text-align: center;">
            <a href="{{ config('app.url') }}" class="action-button">Acessar Sistema</a>
        </div>
    </div>

    <div class="footer">
        <p>Este é um e-mail automático do Sistema de Controle Financeiro.</p>
        <p>Para alterar suas preferências de notificação, acesse as configurações do sistema.</p>
    </div>
</body>
</html>
