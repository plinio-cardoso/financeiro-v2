<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contas Vencendo Hoje</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px 10px 0 0;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .content {
            background: #f7fafc;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }

        .transaction-list {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .transaction-row {
            padding: 16px 20px;
            border-bottom: 1px solid #edf2f7;
        }

        .transaction-row:last-child {
            border-bottom: none;
        }

        .transaction-title {
            font-size: 16px;
            font-weight: bold;
            color: #2d3748;
            margin: 0;
        }

        .transaction-details {
            font-size: 13px;
            color: #718096;
            margin-top: 2px;
        }

        .transaction-amount {
            font-size: 18px;
            font-weight: bold;
            color: #f56565;
        }

        .action-button {
            display: inline-block;
            background: #667eea;
            color: #ffffff !important;
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none !important;
            font-weight: bold;
            margin-top: 20px;
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
            border: 2px solid #f56565;
        }

        .total-amount {
            font-size: 32px;
            font-weight: bold;
            color: #f56565;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>⏰ Contas Vencendo Hoje</h1>
        <p style="margin: 10px 0 0 0; opacity: 0.9;">{{ now()->format('d/m/Y') }}</p>
    </div>

    <div class="content">
        <p>Olá,</p>
        <p>Você tem <strong>{{ count($transactions) }}</strong>
            {{ count($transactions) === 1 ? 'conta que vence' : 'contas que vencem' }} hoje:</p>

        <div class="transaction-list">
            @foreach($transactions as $transaction)
                <div class="transaction-row">
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td align="left" style="vertical-align: top;">
                                <div class="transaction-title">{{ $transaction->title }}</div>
                                @if($transaction->description)
                                    <div class="transaction-details">{{ $transaction->description }}</div>
                                @endif
                                <div class="transaction-details">
                                    Vencimento: <strong>{{ $transaction->due_date->format('d/m/Y') }}</strong>
                                </div>
                            </td>
                            <td align="right" style="vertical-align: top; width: 120px;">
                                <div class="transaction-amount">
                                    R$ {{ number_format($transaction->amount, 2, ',', '.') }}
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            @endforeach
        </div>

        @if(count($transactions) > 1)
            <div class="total-section">
                <div style="color: #718096; margin-bottom: 10px;">Total a Pagar Hoje</div>
                <div class="total-amount">
                    R$ {{ number_format($transactions->sum('amount'), 2, ',', '.') }}
                </div>
            </div>
        @endif

        <p style="margin-top: 30px;">
            Acesse o sistema para gerenciar suas transações e marcar como pagas.
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