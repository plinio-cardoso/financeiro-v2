<?php

namespace App\Livewire;

use Livewire\Attributes\Locked;
use Livewire\Component;
use OpenAI\Laravel\Facades\OpenAI;

class AnalisarWhatsapp extends Component
{
    public string $conversationText = '';

    #[Locked]
    public ?array $analysisResult = null;

    public ?string $errorMessage = null;

    public bool $isAnalyzing = false;

    private const PRICING = [
        'gpt-5-mini-2025-08-07' => [
            'input' => 0.25 / 1_000_000,
            'output' => 2.00 / 1_000_000,
        ],
        'gpt-5-nano-2025-08-07' => [
            'input' => 0.05 / 1_000_000,
            'output' => 0.40 / 1_000_000,
        ],
    ];

    protected function rules(): array
    {
        return [
            'conversationText' => [
                'required',
                'string',
                'min:10',
                'max:10000',
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'conversationText.required' => 'Por favor, cole a conversa do WhatsApp.',
            'conversationText.min' => 'A conversa deve ter pelo menos 10 caracteres.',
            'conversationText.max' => 'A conversa não pode ter mais de 10.000 caracteres.',
        ];
    }

    public function analyze(): void
    {
        $this->validate();

        $this->reset(['analysisResult', 'errorMessage']);
        $this->isAnalyzing = true;

        try {
            $model = config('openai.model', 'gpt-5-mini-2025-08-07');

            $response = OpenAI::chat()->create([
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $this->buildSystemPrompt(),
                    ],
                    [
                        'role' => 'user',
                        'content' => "Analise esta conversa de WhatsApp do grupo de futebol:\n\n{$this->conversationText}",
                    ],
                ],
                'max_completion_tokens' => (int) config('openai.max_tokens', 4096),
                'response_format' => ['type' => 'json_object'],
            ]);

            $content = $response->choices[0]->message->content;
            $data = json_decode($content, true);

            $inputTokens = $response->usage->promptTokens;
            $outputTokens = $response->usage->completionTokens;
            $cost = $this->calculateCost($model, $inputTokens, $outputTokens);

            $this->analysisResult = [
                'summary' => $data['summary'] ?? 'Não foi possível gerar resumo',
                'actions' => $data['actions'] ?? [],
                'inputTokens' => $inputTokens,
                'outputTokens' => $outputTokens,
                'estimatedCostUsd' => $cost,
            ];

            $this->dispatch('conversation-analyzed');
        } catch (\Exception $e) {
            $this->errorMessage = 'Erro ao analisar a conversa: '.$e->getMessage();

            \Log::error('WhatsApp conversation analysis failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        } finally {
            $this->isAnalyzing = false;
        }
    }

    public function resetForm(): void
    {
        $this->reset(['conversationText', 'analysisResult', 'errorMessage']);
    }

    private function buildSystemPrompt(): string
    {
        return <<<'PROMPT'
Você é um assistente especializado em gerenciamento de grupos de futebol.

Analise a conversa de WhatsApp e forneça:

1. RESUMO: Um resumo conciso (2-4 frases) do que foi discutido na conversa
2. AÇÕES: Lista de ações que o sistema poderia executar automaticamente

TIPOS DE AÇÕES POSSÍVEIS:
- add_player: Adicionar jogador à lista de confirmados
- remove_player: Remover jogador da lista
- create_teams: Sortear times balanceados
- schedule_match: Agendar partida
- send_reminder: Enviar lembrete para o grupo
- collect_payment: Registrar/cobrar pagamento (racha)
- update_player_status: Atualizar status do jogador (confirmado/desistiu/reserva)
- count_players: Contar confirmados
- find_replacement: Buscar substituto

Para cada ação, forneça:
- type: tipo da ação (conforme lista acima)
- description: breve descrição do que seria feito
- priority: alta, media, baixa
- data: objeto com dados necessários (ex: nome do jogador, data, valor)

IMPORTANTE:
- Seja específico nas ações baseado no contexto da conversa
- Priorize ações que resolvam problemas mencionados
- Mantenha o resumo objetivo e claro
- Identifique nomes de pessoas mencionadas

Retorne JSON neste formato:
{
  "summary": "Resumo da conversa em 2-4 frases",
  "actions": [
    {
      "type": "add_player",
      "description": "Adicionar João à lista de confirmados",
      "priority": "alta",
      "data": {
        "playerName": "João",
        "status": "confirmado"
      }
    },
    {
      "type": "create_teams",
      "description": "Sortear 2 times com 10 jogadores cada",
      "priority": "media",
      "data": {
        "teamCount": 2,
        "playersPerTeam": 10
      }
    }
  ]
}
PROMPT;
    }

    private function calculateCost(string $model, int $inputTokens, int $outputTokens): float
    {
        $pricing = self::PRICING[$model] ?? self::PRICING['gpt-5-mini-2025-08-07'];

        return ($inputTokens * $pricing['input']) + ($outputTokens * $pricing['output']);
    }

    public function render()
    {
        return view('livewire.analisar-whatsapp');
    }
}
