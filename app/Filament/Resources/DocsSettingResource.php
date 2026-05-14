<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocsSettingResource\Pages;
use App\Models\DocsSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DocsSettingResource extends Resource
{
    protected static ?string $model = DocsSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Documentation';

    protected static ?string $navigationLabel = 'Docs Settings';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Access Control')
                    ->description('Control who can see the /docs page and when.')
                    ->schema([
                        Forms\Components\Toggle::make('is_visible')
                            ->label('Docs Page Visible')
                            ->helperText('Master toggle. If OFF, /docs is never accessible.')
                            ->required(),
                        Forms\Components\DateTimePicker::make('start_date')
                            ->label('Start Date & Time')
                            ->helperText('Docs become publicly accessible from this date.'),
                        Forms\Components\DateTimePicker::make('end_date')
                            ->label('End Date & Time')
                            ->helperText('Docs become inaccessible after this date.'),
                    ])->columns(3),

                Forms\Components\Section::make('Hero Content')
                    ->schema([
                        Forms\Components\TextInput::make('hero_title')
                            ->label('Hero Title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('hero_subtitle')
                            ->label('Hero Subtitle')
                            ->maxLength(500),
                        Forms\Components\Textarea::make('elevator_pitch')
                            ->label('Elevator Pitch')
                            ->helperText('The main pitch displayed at the top of the docs page.')
                            ->rows(4),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('is_visible')
                    ->label('Visible')
                    ->boolean(),
                Tables\Columns\TextColumn::make('hero_title')
                    ->label('Title'),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Start')
                    ->dateTime('M d, Y H:i'),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('End')
                    ->dateTime('M d, Y H:i'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Modified')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocsSettings::route('/'),
            'create' => Pages\CreateDocsSetting::route('/create'),
            'edit' => Pages\EditDocsSetting::route('/{record}/edit'),
        ];
    }
}
