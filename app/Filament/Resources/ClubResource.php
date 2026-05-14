<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClubResource\Pages;
use App\Filament\Resources\ClubResource\RelationManagers;
use App\Models\Club;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClubResource extends Resource
{
    protected static ?string $model = Club::class;

    protected static ?string $navigationLabel = 'Clubs Setting';

    protected static ?string $pluralModelLabel = 'Clubs Setting';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Club Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('type')
                            ->options([
                                'tech' => 'Technology',
                                'arts' => 'Arts & Culture',
                                'sports' => 'Sports',
                                'academic' => 'Academic',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('description')
                            ->nullable()
                            ->rows(3),
                        Forms\Components\FileUpload::make('image_path')
                            ->label('Club Logo')
                            ->image()
                            ->directory('clubs')
                            ->required(),
                        Forms\Components\TextInput::make('website_link')
                            ->label('Website / Facebook Link')
                            ->url()
                            ->nullable(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Logo'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('website_link')
                    ->label('Link')
                    ->limit(30),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'tech' => 'Technology',
                        'arts' => 'Arts & Culture',
                        'sports' => 'Sports',
                        'academic' => 'Academic',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListClubs::route('/'),
            'create' => Pages\CreateClub::route('/create'),
            'edit' => Pages\EditClub::route('/{record}/edit'),
        ];
    }
}
