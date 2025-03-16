<?php

namespace App\Console\Commands;

use App\Models\Interval;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableStyle;

class IntervalsListCommand extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'intervals:list
                            {--left= : Левая граница интервала}
                            {--right= : Правая граница интервала}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Поиск интервалов, пересекающихся с заданным [N, M]';

    /**
     * Execute the console command.
     *
     * @return int
     */
    /**
     * Проверяет наличие необходимых индексов в таблице
     */
    private function checkDatabase(): void {
        if (!Schema::hasTable('intervals')) {
            $this->error('Таблица intervals не существует. Выполните миграцию: php artisan migrate');
        }
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int {
        $left = $this->option('left');
        $right = $this->option('right');

        $validator = Validator::make([
            'left' => $left,
            'right' => $right
        ], [
            'left' => 'required|integer',
            'right' => 'required|integer|gte:left',
        ], [
            'left.required' => 'Параметр --left обязателен',
            'right.required' => 'Параметр --right обязателен',
            'left.integer' => 'Параметр --left должен быть целым числом',
            'right.integer' => 'Параметр --right должен быть целым числом',
            'right.gte' => 'Параметр --right должен быть больше или равен --left',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return 1;
        }

        $this->checkDatabase();

        $left = (int)$left;
        $right = (int)$right;

        $this->info("Поиск интервалов, пересекающихся с [{$left}, {$right}]");

        $query = Interval::intersectingWith($left, $right)
            ->select(['id', 'start', 'end']);

        $intervals = $query->orderBy('start')
            ->orderBy('end', 'asc')
            ->get();

        if ($intervals->isEmpty()) {
            $this->warn("Не найдено интервалов, пересекающихся с [{$left}, {$right}]");
            return 0;
        }

        $tableData = $intervals->map(function($interval) {
            return [
                'id' => $interval->id,
                'interval' => $interval->getIntervalDisplay(),
                'type' => $interval->isRay() ? 'Луч' : 'Отрезок',
                'start' => $interval->start,
                'end' => $interval->isRay() ? '∞' : $interval->end,
            ];
        });

        $tableStyle = new TableStyle();
        $tableStyle->setHorizontalBorderChars('─')
            ->setVerticalBorderChars('│', '│')
            ->setCrossingChars('┼', '┬', '┤', '┴', '├', '┌', '┐', '└', '┘');

        $this->info("Найдено {$intervals->count()} пересекающихся интервалов:");

        $table = new Table($this->output);
        $table->setStyle($tableStyle);
        $table->setHeaders(['ID', 'Интервал', 'Тип', 'Начало', 'Конец']);
        $table->setRows($tableData->toArray());
        $table->render();

        return 0;
    }
}
