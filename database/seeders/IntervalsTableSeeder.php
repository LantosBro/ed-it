<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IntervalsTableSeeder extends Seeder {
    /**
     * Константы для настройки генерации данных
     */
    public const TOTAL_RECORDS = 10000;
    public const BATCH_SIZE = 1000;
    public const RAY_PROBABILITY = 0.2; // 20% шанс на создание луча
    public const MIN_START = 1;
    public const MAX_START = 1000;
    public const MAX_LENGTH = 500;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $this->command->info('Начинаем заполнение таблицы intervals...');
        $this->command->getOutput()->progressStart(self::TOTAL_RECORDS);

        $chunks = [];

        for ($i = 0; $i < self::TOTAL_RECORDS; $i++) {
            $start = random_int(self::MIN_START, self::MAX_START);

            // Определяем, будет ли это луч (end = NULL) с вероятностью RAY_PROBABILITY
            $isRay = (random_int(1, 100) <= (self::RAY_PROBABILITY * 100));
            $end = $isRay ? null : $start + random_int(1, self::MAX_LENGTH);

            $chunks[] = [
                'start' => $start,
                'end' => $end,
            ];

            if (count($chunks) >= self::BATCH_SIZE) {
                DB::table('intervals')->insert($chunks);
                $chunks = [];
                $this->command->getOutput()->progressAdvance(self::BATCH_SIZE);
            }
        }

        if (!empty($chunks)) {
            DB::table('intervals')->insert($chunks);
            $this->command->getOutput()->progressAdvance(count($chunks));
        }

        $this->command->getOutput()->progressFinish();
        $this->command->info('Заполнение таблицы intervals завершено успешно!');
    }
}
