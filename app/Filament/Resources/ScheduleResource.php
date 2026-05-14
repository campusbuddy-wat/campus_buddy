<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduleResource\Pages;
use App\Filament\Resources\ScheduleResource\RelationManagers;
use App\Models\Schedule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static ?string $navigationLabel = 'Routine Setting';

    protected static ?string $pluralModelLabel = 'Routine Setting';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Routine Details')
                    ->schema([
                        Forms\Components\TextInput::make('course_code')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('course_title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('teacher_initial')
                            ->required()
                            ->maxLength(10),
                        Forms\Components\TextInput::make('department')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('batch')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('section')
                            ->required()
                            ->maxLength(10),
                        Forms\Components\TextInput::make('major')
                            ->nullable()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('room_no')
                            ->required()
                            ->maxLength(50),
                        Forms\Components\Select::make('type')
                            ->options([
                                'theory' => 'Theory',
                                'lab' => 'Lab',
                            ])
                            ->reactive()
                            ->required(),
                        Forms\Components\TextInput::make('lab_section')
                            ->label('Lab Section (e.g., B1)')
                            ->visible(fn ($get) => $get('type') === 'lab')
                            ->nullable(),
                        Forms\Components\Select::make('day')
                            ->options([
                                'Monday' => 'Monday',
                                'Tuesday' => 'Tuesday',
                                'Wednesday' => 'Wednesday',
                                'Thursday' => 'Thursday',
                                'Friday' => 'Friday',
                                'Saturday' => 'Saturday',
                                'Sunday' => 'Sunday',
                            ])
                            ->required(),
                        Forms\Components\Select::make('time_slot')
                            ->options([
                                '8.30 am-10.00 am' => '8.30 am-10.00 am',
                                '10.00 am-11.30 am' => '10.00 am-11.30 am',
                                '11.30 am-1.00 pm' => '11.30 am-1.00 pm',
                                '1.00 pm-2.30 pm' => '1.00 pm-2.30 pm',
                                '2.30 pm-4.00 pm' => '2.30 pm-4.00 pm',
                                '4.00 pm-5.30 pm' => '4.00 pm-5.30 pm',
                            ])
                            ->required(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('course_code')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('course_title')->searchable(),
                Tables\Columns\TextColumn::make('teacher_initial'),
                Tables\Columns\TextColumn::make('department')->searchable(isIndividual: true),
                Tables\Columns\TextColumn::make('batch')->searchable(isIndividual: true),
                Tables\Columns\TextColumn::make('section')->searchable(isIndividual: true),
                Tables\Columns\TextColumn::make('day')->sortable(),
                Tables\Columns\TextColumn::make('time_slot'),
            ])
            ->searchPlaceholder('Search schedules...')
            ->filters([
                //
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
            'index' => Pages\ListSchedules::route('/'),
            'create' => Pages\CreateSchedule::route('/create'),
            'edit' => Pages\EditSchedule::route('/{record}/edit'),
        ];
    }
}
