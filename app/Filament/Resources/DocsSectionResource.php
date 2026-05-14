<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocsSectionResource\Pages;
use App\Models\DocsSection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DocsSectionResource extends Resource
{
    protected static ?string $model = DocsSection::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $navigationGroup = 'Documentation';

    protected static ?string $navigationLabel = 'Docs Sections';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Section Details')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('icon')
                            ->label('FontAwesome Icon Class')
                            ->placeholder('fa-lightbulb')
                            ->helperText('FontAwesome icon class without "fas" prefix.'),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Toggle::make('is_visible')
                            ->label('Visible')
                            ->default(true),
                    ])->columns(3),
                Forms\Components\Section::make('Content')
                    ->schema([
                        Forms\Components\RichEditor::make('content')
                            ->label('Section Content (HTML)')
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug'),
                Tables\Columns\IconColumn::make('is_visible')
                    ->label('Visible')
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('M d, Y')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocsSections::route('/'),
            'create' => Pages\CreateDocsSection::route('/create'),
            'edit' => Pages\EditDocsSection::route('/{record}/edit'),
        ];
    }
}
