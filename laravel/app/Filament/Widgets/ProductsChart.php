<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class ProductsChart extends ChartWidget
{
    protected static ?string $heading = 'Product analysis per month';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $data = $this->getProductsPerMonth();
        return [
            'datasets' => [
                [
                    'label' => 'Products per month',
                    'data' => $data['productsPerMonth']
                ]
            ],
            'labels' => $data['months']
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    private function getProductsPerMonth(): array
    {
        try {
            $now = Carbon::now();
            $productsPerMonth = [];
            $months = collect(range(1, 12))->map(function ($month) use ($now, &$productsPerMonth) {
                $date = Carbon::parse($now->month($month)->format('Y-m'));
                $count = Product::whereMonth('created_at', $date)->count();
                $productsPerMonth[] = $count;

                return $date->format('M');
            })->toArray();

            return [
                'productsPerMonth' => $productsPerMonth,
                'months' => $months,
            ];
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }
}
