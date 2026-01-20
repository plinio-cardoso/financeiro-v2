<div class="space-y-8 pb-12">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- Próximas Despesas & Vencidas --}}
        <div class="overflow-hidden bg-white shadow sm:rounded-lg dark:bg-gray-800 flex flex-col" x-intersect.once="$wire.loadUpcomingExpenses()">
            <div class="px-8 py-6 border-b border-gray-50 dark:border-gray-700/30 flex items-center justify-between">
                <h3 class="text-xs font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">Próximas Despesas</h3>
                <a href="{{ route('transactions.index') }}" 
                    class="text-[10px] font-black uppercase tracking-widest text-emerald-800 dark:text-[#4ECDC4] hover:opacity-70 transition-opacity flex items-center gap-1">
                    Ver todas
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                </a>
            </div>
            
            <div class="p-6 flex-1">
                <div class="space-y-8">
                    @forelse($this->upcomingExpensesGrouped as $timestamp => $dayExpenses)
                        @php
                            $date = \Carbon\Carbon::createFromTimestamp($timestamp);
                        @endphp
                        <div class="space-y-4">
                            <h4 class="text-xs font-black uppercase tracking-widest text-gray-500 dark:text-gray-400 px-1">
                                @if($date->isToday()) HOJE @elseif($date->isTomorrow()) AMANHÃ @else {{ $date->translatedFormat('D, d M') }} @endif
                            </h4>

                            @foreach($dayExpenses as $expense)
                                <div class="relative flex items-center justify-between p-4 rounded-xl hover:bg-gray-50/20 dark:hover:bg-gray-900/40 transition-all group border border-transparent hover:border-gray-100 dark:hover:border-gray-700/50">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                                            <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <p class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ $expense->title }}</p>
                                                @if($expense->isOverdue())
                                                    <span class="px-2 py-0.5 text-[8px] font-black uppercase tracking-tighter bg-rose-100 text-rose-600 dark:bg-rose-900 dark:text-rose-400 rounded-md shadow-sm">Vencido</span>
                                                @endif
                                            </div>
                                            <div class="flex gap-1 mt-1">
                                                @foreach($expense->tags as $tag)
                                                    <span class="text-[8px] font-black uppercase tracking-widest px-1.5 py-0.5 rounded-md" style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}">
                                                        {{ $tag->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-6">
                                        <div class="text-right">
                                            <span class="block text-sm font-black text-gray-900 dark:text-gray-100">{{ $expense->getFormattedAmount() }}</span>
                                        </div>
                                        <button wire:click="markAsPaid({{ $expense->id }})"
                                            class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 rounded-xl hover:bg-emerald-100 dark:hover:bg-emerald-500/20 transition-all text-[10px] font-black uppercase tracking-widest border border-emerald-500/20 dark:border-none shadow-sm">
                                            PAGAR
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @empty
                        <div class="py-12 text-center">
                            <p class="text-xs font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">Tudo em dia!</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Histórico Recente --}}
        <div class="overflow-hidden bg-white shadow sm:rounded-lg dark:bg-gray-800 flex flex-col" x-intersect.once="$wire.loadRecentActivity()">
            <div class="px-8 py-6 border-b border-gray-50 dark:border-gray-700/30 flex items-center justify-between">
                <h3 class="text-xs font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">Atividades Recentes</h3>
                <span class="text-[10px] font-black uppercase tracking-widest text-gray-900 dark:text-gray-100">Últimas 5</span>
            </div>
            
            <div class="p-4 flex-1">
                <div class="space-y-1">
                    @foreach($recentActivity as $activity)
                        <div class="flex items-center justify-between p-4 rounded-xl hover:bg-gray-50/50 dark:hover:bg-gray-900/10 transition-all border border-transparent hover:border-gray-100/50 dark:hover:border-gray-700/20">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-lg bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-700 flex items-center justify-center">
                                    <svg class="w-5 h-5 {{ $activity->getTypeColorClass() }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        @if($activity->type->value === 'debit')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 17l-4 4m0 0l-4-4m4 4V3" />
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7l4-4m0 0l4 4m-4-4v18" />
                                        @endif
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ $activity->title }}</p>
                                    <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-widest font-black mt-0.5">
                                        {{ $activity->updated_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-black {{ $activity->getAmountColorClass() }}">
                                    {{ $activity->getSignPrefix() }} {{ $activity->getFormattedAmount() }}
                                </p>
                                @if($activity->status->value === 'paid')
                                    <span class="text-[8px] font-black uppercase tracking-widest text-emerald-600 px-1.5 py-0.5 bg-emerald-50 dark:bg-emerald-500/10 rounded-md">Pago</span>
                                @else
                                    <span class="text-[8px] font-black uppercase tracking-widest text-yellow-700 px-1.5 py-0.5 bg-yellow-50 dark:bg-yellow-600/10 rounded-md">Pendente</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Gráficos --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Gastos por Categoria --}}
        <div class="lg:col-span-1 bg-white dark:bg-gray-800 shadow sm:rounded-lg p-8" x-intersect.once="$wire.loadExpensesByTag()">
            <h3 class="text-xs font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-8">Despesas por Categoria</h3>
            <div id="chart-tags" class="min-h-[300px]"></div>
        </div>

        {{-- Evolução de Gastos --}}
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 shadow sm:rounded-lg p-8 flex flex-col" x-intersect.once="$wire.loadMonthlyComparison()">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-xs font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">Fluxo de Despesas Mensal</h3>
                    <p class="text-2xl font-black text-gray-900 dark:text-gray-100 mt-1">R$ {{ number_format($this->monthlyExpenseTotal, 2, ',', '.') }}</p>
                    <p class="text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest mt-1">Total de saídas em {{ now()->translatedFormat('F') }}</p>
                </div>
            </div>
            <div id="chart-monthly" class="min-h-[300px] flex-1"></div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        let tagChart = null;
        let monthlyChart = null;

        function getChartStyles() {
            const isDark = document.documentElement.classList.contains('dark');
            return {
                textColor: isDark ? '#94a3b8' : '#475569',
                chartTheme: isDark ? 'dark' : 'light',
                isDark: isDark
            };
        }

        function initializeTagChart(tagData) {
            const { textColor, chartTheme } = getChartStyles();

            // Destroy existing chart if it exists
            if (tagChart) {
                tagChart.destroy();
            }

            // Don't render if no data
            if (!tagData || tagData.length === 0) {
                console.log('No tag data available');
                return;
            }

            console.log('Rendering tag chart with data:', tagData);

            const tagOptions = {
                chart: {
                    type: 'donut',
                    height: 350,
                    fontFamily: 'Inter, sans-serif',
                    background: 'transparent'
                },
                theme: { mode: chartTheme },
                series: tagData.map(t => parseFloat(t.total)),
                labels: tagData.map(t => t.tag_name),
                colors: tagData.map(t => t.tag_color),
                stroke: { show: false },
                dataLabels: { enabled: false },
                legend: {
                    position: 'bottom',
                    labels: { colors: textColor, useSeriesColors: false },
                    markers: { strokeWidth: 0, radius: 12, offsetX: -5 },
                    itemMargin: { horizontal: 10, vertical: 8 }
                },
                tooltip: { theme: chartTheme },
                plotOptions: { pie: { donut: { size: '75%' } } }
            };

            tagChart = new ApexCharts(document.querySelector("#chart-tags"), tagOptions);
            tagChart.render();
        }

        function initializeMonthlyChart(monthlyData) {
            const { textColor, chartTheme, isDark } = getChartStyles();

            // Destroy existing chart if it exists
            if (monthlyChart) {
                monthlyChart.destroy();
            }

            // Don't render if no data
            if (!monthlyData || monthlyData.length === 0) {
                console.log('No monthly data available');
                return;
            }

            console.log('Rendering monthly chart with data:', monthlyData);

            const monthlyOptions = {
                chart: {
                    type: 'area',
                    height: 350,
                    fontFamily: 'Inter, sans-serif',
                    toolbar: { show: false },
                    background: 'transparent'
                },
                theme: { mode: chartTheme },
                series: [{
                    name: 'Total Gasto',
                    data: monthlyData.map(m => parseFloat(m.total))
                }],
                xaxis: {
                    categories: monthlyData.map(m => {
                        const date = new Date(m.year, m.month - 1);
                        return date.toLocaleString('pt-BR', { month: 'short' }).toUpperCase();
                    }),
                    labels: { style: { colors: textColor, fontWeight: 700, fontSize: '10px' } },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: { labels: { style: { colors: textColor, fontWeight: 600 } } },
                dataLabels: { enabled: false },
                colors: ['#4ECDC4'],
                fill: {
                    type: 'gradient',
                    gradient: { shadeIntensity: 1, opacityFrom: 0.5, opacityTo: 0, stops: [0, 90, 100] }
                },
                stroke: { curve: 'smooth', width: 4 },
                grid: { borderColor: isDark ? '#1e293b' : '#f1f5f9', strokeDashArray: 4 },
                tooltip: {
                    theme: chartTheme,
                    style: {
                        fontSize: '12px',
                        fontFamily: 'Inter, sans-serif'
                    },
                    x: {
                        show: true
                    },
                    y: {
                        formatter: function(value) {
                            return 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        }
                    },
                    custom: function({ series, seriesIndex, dataPointIndex, w }) {
                        const value = series[seriesIndex][dataPointIndex];
                        const category = w.globals.labels[dataPointIndex];

                        return '<div style="padding: 10px; background: ' + (isDark ? '#1e293b' : '#ffffff') + '; border: 1px solid ' + (isDark ? '#334155' : '#e2e8f0') + '; border-radius: 6px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">' +
                               '<div style="color: ' + (isDark ? '#94a3b8' : '#64748b') + '; font-size: 10px; font-weight: 700; text-transform: uppercase; margin-bottom: 4px;">' + category + '</div>' +
                               '<div style="color: ' + (isDark ? '#f1f5f9' : '#0f172a') + '; font-size: 14px; font-weight: 700;">R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</div>' +
                               '</div>';
                    }
                }
            };

            monthlyChart = new ApexCharts(document.querySelector("#chart-monthly"), monthlyOptions);
            monthlyChart.render();
        }

        // Listen for Livewire events when data is loaded
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('expensesByTagLoaded', (event) => {
                console.log('expensesByTagLoaded event received:', event);
                // Small delay to ensure DOM is updated
                setTimeout(() => initializeTagChart(event.data), 100);
            });

            Livewire.on('monthlyComparisonLoaded', (event) => {
                console.log('monthlyComparisonLoaded event received:', event);
                // Small delay to ensure DOM is updated
                setTimeout(() => initializeMonthlyChart(event.data), 100);
            });
        });

        // Reinitialize charts when theme changes (only if data already loaded)
        window.addEventListener('theme-changed', () => {
            // Charts will be re-rendered on next data load
            if (tagChart) tagChart.destroy();
            if (monthlyChart) monthlyChart.destroy();
        });
    </script>
    @endpush
</div>