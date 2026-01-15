<?php

namespace App\Filament\Admin\Widgets;

use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;

class BugReproductionChart extends ChartWidget
{
    use HasFiltersSchema;

    protected ?string $heading = 'Bug Reproduction Chart (3 Searchable Dependent Selects)';

    protected int|string|array $columnSpan = 'full';

    /**
     * WORKAROUND: Comment out the method below to reproduce the bug.
     * When searching in a searchable select within filtersSchema, Filament attempts
     * to call a `filters()` method via reflection but fails because `HasFiltersSchema`
     * only provides `filtersSchema()`.
     */
    // public function filters(Schema $schema): Schema
    // {
    //     return $this->filtersSchema($schema);
    // }

    public function filtersSchema(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('category_id')
                ->label('Category')
                ->searchable()
                ->options([
                    'electronics' => 'Electronics',
                    'furniture' => 'Furniture',
                ])
                ->placeholder('Select a category')
                ->live(),

            Select::make('subcategory_id')
                ->label('Subcategory')
                ->searchable()
                ->options(function (Get $get) {
                    $category = $get('category_id');

                    return [
                        'electronics' => [
                            'phones' => 'Phones',
                            'laptops' => 'Laptops',
                        ],
                        'furniture' => [
                            'chairs' => 'Chairs',
                            'tables' => 'Tables',
                        ],
                    ];
                })
                ->placeholder('Select a subcategory')
                ->live()
                ->afterStateUpdated(fn (Set $set) => $set('item_id', null)),
        ]);
    }

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Sample Data',
                    'data' => [10, 20, 30],
                ],
            ],
            'labels' => ['A', 'B', 'C'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
