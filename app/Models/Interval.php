<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interval extends Model {
    use HasFactory;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'start',
        'end',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start' => 'integer',
        'end' => 'integer',
    ];

    /**
     * Проверяет, является ли интервал лучом.
     *
     * @return bool
     */
    public function isRay(): bool {
        return is_null($this->end);
    }

    /**
     * Возвращает форматированное представление интервала.
     *
     * @return string
     */
    public function getIntervalDisplay(): string {
        return '[' . $this->start . ', ' . ($this->isRay() ? '∞' : $this->end) . ']';
    }

    /**
     * Проверяет, пересекается ли интервал с заданным.
     *
     * @param int $left Левая граница
     * @param int $right Правая граница
     * @return bool
     */
    public function intersectsWith(int $left, int $right): bool {
        return $this->start <= $right && ($this->isRay() || $this->end >= $left);
    }

    /**
     * Scope для поиска интервалов, пересекающихся с заданным.
     *
     * @param Builder $query
     * @param int $left Левая граница
     * @param int $right Правая граница
     * @return Builder
     */
    public function scopeIntersectingWith(Builder $query, int $left, int $right): Builder {
        return $query->where(function($q) use ($left, $right) {
            $q->whereNotNull('end')
                ->where('start', '<=', $right)
                ->where('end', '>=', $left);
        })->orWhere(function($q) use ($right) {
            $q->whereNull('end')
                ->where('start', '<=', $right);
        });
    }
}
