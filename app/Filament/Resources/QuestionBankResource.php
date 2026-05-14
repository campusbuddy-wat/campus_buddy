<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionBankResource\Pages;
use App\Filament\Resources\QuestionBankResource\RelationManagers;
use App\Models\QuestionBank;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QuestionBankResource extends Resource
{
    protected static ?string $model = QuestionBank::class;
    
    protected static ?string $navigationLabel = 'QuestionBank Setting';

    protected static ?string $pluralModelLabel = 'QuestionBank Setting';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Academic Details')
                    ->schema([
                        Forms\Components\TextInput::make('department')
                            ->placeholder('e.g. SWE (AI will auto-fill)')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('course_code')
                            ->placeholder('e.g. SWE441 (AI will auto-fill)')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('course_name')
                            ->placeholder('e.g. Software Quality Assurance (AI will auto-fill)')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('year_semester')
                            ->label('Semester/Year')
                            ->placeholder('e.g. Fall 2025 (AI will auto-fill)')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Question Content')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Card Title')
                            ->placeholder('e.g. Midterm 2025 (AI will auto-fill)')
                            ->maxLength(255),
                        Forms\Components\Select::make('difficulty')
                            ->options([
                                'Easy' => 'Easy',
                                'Medium' => 'Medium',
                                'Hard' => 'Hard',
                            ])
                            ->required()
                            ->default('Medium'),
                        Forms\Components\TextInput::make('question_heading')
                            ->placeholder('e.g. Q1: Testing Fundamentals (AI will auto-fill)')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('sub_questions')
                            ->label('Sub Questions (One per line)')
                            ->placeholder("AI will automatically extract the core questions.")
                            ->rows(5)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('tags')
                            ->placeholder('testing, quality, swe')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Administration')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->label('Uploaded By'),
                        Forms\Components\FileUpload::make('file_path')
                            ->label('Question Files (PDF/Images)')
                            ->directory('question_banks')
                            ->multiple()
                            ->reorderable()
                            ->appendFiles()
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'])
                            ->maxSize(15360),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->required()
                            ->default('approved'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('department')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('course_code')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->limit(30)
                    ->searchable(),
                Tables\Columns\TextColumn::make('year_semester')
                    ->label('Year/Sem')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'primary' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Uploader')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('department')
                    ->searchable(),
                Tables\Filters\SelectFilter::make('year_semester')
                    ->label('Semester/Year')
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListQuestionBanks::route('/'),
            'create' => Pages\CreateQuestionBank::route('/create'),
            'edit' => Pages\EditQuestionBank::route('/{record}/edit'),
        ];
    }
}
