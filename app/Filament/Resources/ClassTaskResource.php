<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClassTaskResource\Pages;
use App\Filament\Resources\ClassTaskResource\RelationManagers;
use App\Models\ClassTask;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClassTaskResource extends Resource
{
    protected static ?string $model = ClassTask::class;

    protected static ?string $navigationLabel = 'ClassTask Setting';

    protected static ?string $pluralModelLabel = 'ClassTask Setting';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('ClassTask Details')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->options([
                                'assignment' => 'Assignment',
                                'quiz' => 'Quiz',
                                'presentation' => 'Presentation',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('course_code')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('topic')
                            ->nullable()
                            ->maxLength(255),
                        Forms\Components\DateTimePicker::make('deadline')
                            ->required(),
                        Forms\Components\TextInput::make('duration_or_slot')
                            ->nullable()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('department')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('batch')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('section')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('major')
                            ->nullable()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('tip_1')
                            ->nullable(),
                        Forms\Components\Textarea::make('tip_2')
                            ->nullable(),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->searchable(isIndividual: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('course_code')
                    ->searchable(isIndividual: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('department')
                    ->searchable(isIndividual: true),
                Tables\Columns\TextColumn::make('deadline')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'assignment' => 'Assignment',
                        'quiz' => 'Quiz',
                        'presentation' => 'Presentation',
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
            'index' => Pages\ListClassTasks::route('/'),
            'create' => Pages\CreateClassTask::route('/create'),
            'edit' => Pages\EditClassTask::route('/{record}/edit'),
        ];
    }
}
