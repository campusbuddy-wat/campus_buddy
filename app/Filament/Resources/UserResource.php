<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Mail\CrAccountApproved;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Users';

    protected static ?string $modelLabel = 'User';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('student_id')
                            ->label('Student ID')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),

                        Forms\Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => !empty($state) ? Hash::make($state) : null)
                            ->dehydrated(fn ($state) => !empty($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->maxLength(255)
                            ->hint(fn (string $operation) => $operation === 'edit' ? 'Leave blank to keep current password' : null),
                    ])->columns(2),

                Forms\Components\Section::make('Role & Access')
                    ->schema([
                        Forms\Components\Select::make('role')
                            ->label('Role')
                            ->options([
                                'admin'   => '🔴 Admin',
                                'cr'      => '🟡 Class Representative (CR)',
                                'student' => '🟢 Student',
                            ])
                            ->default('student')
                            ->required()
                            ->live(),

                        Forms\Components\Toggle::make('is_approved')
                            ->label('Account Approved')
                            ->helperText('Unapproved CR accounts cannot log in. Students are always approved.')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('danger'),
                    ])->columns(2),

                Forms\Components\Section::make('Academic Context')
                    ->description('These fields determine what data the user sees on their dashboard.')
                    ->schema([
                        Forms\Components\TextInput::make('department')
                            ->label('Department')
                            ->placeholder('e.g. SWE, CSE')
                            ->maxLength(100),

                        Forms\Components\TextInput::make('batch')
                            ->label('Batch')
                            ->placeholder('e.g. 40')
                            ->maxLength(20),

                        Forms\Components\TextInput::make('semester')
                            ->label('Semester')
                            ->placeholder('e.g. 7th')
                            ->maxLength(20),

                        Forms\Components\TextInput::make('section')
                            ->label('Section')
                            ->placeholder('e.g. A, B')
                            ->maxLength(10),

                        Forms\Components\TextInput::make('major')
                            ->label('Major')
                            ->placeholder('e.g. DS, CS, Robotics')
                            ->maxLength(100),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email copied!')
                    ->icon('heroicon-m-envelope'),

                Tables\Columns\TextColumn::make('student_id')
                    ->label('Student ID')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\BadgeColumn::make('role')
                    ->label('Role')
                    ->sortable()
                    ->searchable()
                    ->colors([
                        'danger'  => 'admin',
                        'warning' => 'cr',
                        'success' => 'student',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'admin'   => 'Admin',
                        'cr'      => 'CR',
                        'student' => 'Student',
                        default   => ucfirst($state),
                    }),

                Tables\Columns\IconColumn::make('is_approved')
                    ->label('Approved')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('department')
                    ->label('Dept.')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('batch')
                    ->label('Batch')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('section')
                    ->label('Section')
                    ->searchable(),

                Tables\Columns\TextColumn::make('major')
                    ->label('Major')
                    ->searchable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Filter by Role')
                    ->options([
                        'admin'   => 'Admin',
                        'cr'      => 'Class Representative (CR)',
                        'student' => 'Student',
                    ])
                    ->placeholder('All Roles'),

                SelectFilter::make('department')
                    ->label('Filter by Department')
                    ->options(
                        User::query()->whereNotNull('department')->pluck('department', 'department')->unique()->toArray()
                    )
                    ->placeholder('All Departments'),

                SelectFilter::make('batch')
                    ->label('Filter by Batch')
                    ->options(
                        User::query()->whereNotNull('batch')->pluck('batch', 'batch')->unique()->toArray()
                    )
                    ->placeholder('All Batches'),

                TernaryFilter::make('is_approved')
                    ->label('Approval Status')
                    ->placeholder('All Users')
                    ->trueLabel('Approved Only')
                    ->falseLabel('Pending Approval'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                // Quick approve action for CRs
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (User $record): bool => !$record->is_approved)
                    ->requiresConfirmation()
                    ->modalHeading('Approve CR Account')
                    ->modalDescription('This will allow the CR to log in to Campus Buddy and send them an approval email.')
                    ->action(function (User $record) {
                        $record->update(['is_approved' => true]);

                        // Send approval email notification
                        try {
                            Mail::to($record->email)->send(new CrAccountApproved($record));

                            Notification::make()
                                ->title('✅ Account Approved')
                                ->body('Approval email sent to ' . $record->email)
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('✅ Account Approved')
                                ->body('Account approved, but email failed to send: ' . $e->getMessage())
                                ->warning()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('revoke')
                    ->label('Revoke')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (User $record): bool => $record->is_approved && $record->role === 'cr')
                    ->requiresConfirmation()
                    ->modalHeading('Revoke CR Access')
                    ->modalDescription('This will prevent the CR from logging in.')
                    ->action(fn (User $record) => $record->update(['is_approved' => false])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}