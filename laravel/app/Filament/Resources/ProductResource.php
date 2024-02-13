<?php

namespace App\Filament\Resources;

use App\Enums\ProductTypeEnum;
use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationGroup = 'Shop';
    // protected static ?string $navigationLabel = 'Test';
    protected static ?int $navigationSort = 0;

    protected static ?string $recordTitleAttribute = 'name';

    protected static int $globalSearchResultsLimit = 20;

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'slug', 'description', 'brand.name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Brand' => $record->brand->name,
            'Description' => $record->description,
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['brand']);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        Section::make('Products')
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (string $operation, $state, Set $set) {
                                        if ($operation !== 'create') {
                                            return;
                                        }

                                        $set('slug', Str::slug($state));
                                    }),
                                TextInput::make('slug')
                                    ->disabled()
                                    ->dehydrated()->required()
                                    ->unique(Product::class, 'slug', ignoreRecord: true),
                                MarkdownEditor::make('description')->columnSpan('full')
                            ])->columns(2),
                        Section::make('Pricing & Inventory')
                            ->schema([
                                TextInput::make('sku')
                                    ->label("SKU (Stock Keeping Unit)")
                                    ->unique()
                                    ->required(),
                                TextInput::make('price')
                                    ->required()
                                    ->numeric()
                                    ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/']),
                                TextInput::make('quantity')
                                    // ->rules(['integer' , 'min:0']),
                                    ->numeric()
                                    ->minValue(0)
                                    ->required(),
                                Select::make('type')
                                    ->options([
                                        'downloadable' => ProductTypeEnum::DOWNLOADABLE->value,
                                        'deliverable' => ProductTypeEnum::DELIVERABLE->value,
                                    ])
                                    ->required(),
                            ])->columns(2),
                    ]),
                Group::make()
                    ->schema([
                        Section::make('Status')
                            ->schema([
                                Toggle::make('is_visible')
                                    ->label('Visibility')
                                    ->helperText('Enable or disable product visibility')
                                    ->default(true),
                                Toggle::make('is_featured')
                                    ->label('Featured')
                                    ->helperText('Enable or disable product featured status'),
                                DatePicker::make('published_at')
                                    ->label('Availability')
                                    ->default(now()),
                            ]),
                        Section::make('Product image')
                            ->schema([
                                FileUpload::make('image')
                                    ->directory('form-attachment')
                                    ->preserveFilenames()
                                    ->image()
                                    ->imageEditor(),
                            ])->collapsible(),
                        Section::make('Associated Brand')
                            ->schema([
                                Select::make('brand_id')
                                    ->relationship('brand', 'name'),
                            ]),

                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image'),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('brand.name')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_visible')
                    ->boolean()
                    // ->toggleable()
                    ->label('Visibility'),
                TextColumn::make('price')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity'),
                TextColumn::make('published_at')
                    ->date()
                    ->sortable(),
                TextColumn::make('type'),
            ])
            ->filters([
                TernaryFilter::make('is_visible')
                    ->label('Visibility')
                    ->boolean()
                    ->trueLabel('Visible Products')
                    ->falseLabel('Hidden Products')
                    ->native(false),

                SelectFilter::make('brand')
                    ->relationship('brand', 'name'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
