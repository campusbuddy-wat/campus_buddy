<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaterialResource\Pages;
use App\Filament\Resources\MaterialResource\RelationManagers;
use App\Models\Material;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MaterialResource extends Resource
{
    protected static ?string $model = Material::class;

    protected static ?string $navigationLabel = 'Pdf & Note Setting';

    protected static ?string $pluralModelLabel = 'Pdf & Note Setting';

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Academic Information')
                    ->schema([
                        Forms\Components\TextInput::make('course_code')
                            ->required()
                            ->placeholder('e.g. SWE441')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('department')
                            ->required()
                            ->placeholder('e.g. SWE')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('batch')
                            ->required()
                            ->placeholder('e.g. 40')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('section')
                            ->required()
                            ->placeholder('e.g. B')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('major')
                            ->placeholder('e.g. Software Engineering')
                            ->maxLength(255)
                            ->default(null),
                    ])->columns(2),

                Forms\Components\Section::make('Material Content')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->placeholder('e.g. Lecture 05: Logic Gates')
                            ->maxLength(255),
                        Forms\Components\Select::make('type')
                            ->options([
                                'class_material' => 'Class Material',
                                'suggestion' => 'Suggestion',
                                'short_note' => 'Short Note',
                            ])
                            ->required()
                            ->default('class_material'),
                    ])->columns(2),

                Forms\Components\Section::make('Administration & File')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required()
                            ->label('Uploaded By'),
                        Forms\Components\FileUpload::make('file_path')
                            ->directory('uploads/materials')
                            ->required()
                            ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->label('Material File'),
                        Forms\Components\TextInput::make('file_extension')
                            ->required()
                            ->placeholder('pdf, docx')
                            ->maxLength(10),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('course_code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'class_material' => 'success',
                        'suggestion' => 'warning',
                        'short_note' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('department')
                    ->searchable(isIndividual: true),
                Tables\Columns\TextColumn::make('batch')
                    ->searchable(isIndividual: true),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Uploaded By')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'class_material' => 'Class Material',
                        'suggestion' => 'Suggestion',
                        'short_note' => 'Short Note',
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
            'index' => Pages\ListMaterials::route('/'),
            'create' => Pages\CreateMaterial::route('/create'),
            'edit' => Pages\EditMaterial::route('/{record}/edit'),
        ];
    }
}
