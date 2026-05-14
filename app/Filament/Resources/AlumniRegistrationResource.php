<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AlumniRegistrationResource\Pages;
use App\Models\AlumniRegistration;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use App\Models\User;

class AlumniRegistrationResource extends Resource
{
    protected static ?string $model = AlumniRegistration::class;

    protected static ?string $navigationLabel = 'Alumni Approval';

    protected static ?string $pluralModelLabel = 'Alumni Requests';

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal Details')
                    ->schema([
                        Forms\Components\TextInput::make('full_name')->required(),
                        Forms\Components\TextInput::make('email')->email()->required(),
                        Forms\Components\TextInput::make('phone')->nullable(),
                        Forms\Components\FileUpload::make('profile_image')->image()->directory('alumni/profiles')->nullable(),
                    ])->columns([
                        'default' => 2,
                    ]),

                Forms\Components\Section::make('Academic & Career')
                    ->schema([
                        Forms\Components\TextInput::make('student_id')->required(),
                        Forms\Components\TextInput::make('department')->required(),
                        Forms\Components\TextInput::make('batch')->required(),
                        Forms\Components\TextInput::make('graduation_year')->required(),
                        Forms\Components\TextInput::make('current_position')->placeholder('e.g. Lead Developer')->required(),
                        Forms\Components\TextInput::make('company')->placeholder('e.g. Brain Station 23')->required(),
                        Forms\Components\FileUpload::make('company_logo')->image()->directory('alumni/logos')->nullable(),
                        Forms\Components\TextInput::make('category')->placeholder('e.g. software-engineering')->required(),
                        Forms\Components\TextInput::make('linkedin_url')->url()->nullable(),
                    ])->columns([
                        'default' => 2,
                    ]),

                Forms\Components\Section::make('Review & Approve')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approve & Create Card',
                                'rejected' => 'Reject',
                             ])
                            ->required(),
                        Forms\Components\Textarea::make('admin_note')
                            ->placeholder('Verification notes...')
                            ->rows(2),
                    ]),

                Forms\Components\Section::make('Card Customization & Styling (Advanced)')
                    ->schema([
                        Forms\Components\TextInput::make('badge_text')->placeholder('e.g. PREMIUM, EXECUTIVE')->nullable(),
                        Forms\Components\TextInput::make('badge_style')->placeholder('e.g. badge-gold, badge-red')->nullable(),
                        Forms\Components\TextInput::make('subtitle')->placeholder('Optional award or sub-info')->nullable(),
                        Forms\Components\TextInput::make('top_img_class')->placeholder('e.g. img-contain-70, img-cover-full')->nullable(),
                        Forms\Components\TextInput::make('profile_img_class')->placeholder('e.g. profile-pos-10')->nullable(),
                        Forms\Components\TextInput::make('container_class')->placeholder('e.g. card-top-logo-container')->nullable(),
                    ])->columns([
                        'default' => 2,
                    ])->collapsed()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('profile_image')->circular()->label('Avatar'),
                Tables\Columns\TextColumn::make('full_name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('current_position')->label('Role')->searchable(),
                Tables\Columns\TextColumn::make('company')->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),
                Tables\Columns\TextColumn::make('created_at')->dateTime('M d, Y')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function (AlumniRegistration $record) {
                        // Hook: occur automatically if status is changed to approved
                        if ($record->status === 'approved') {
                            $user = User::where('email', $record->email)->first();
                            
                            if (!$user) {
                                // Create new user if they don't exist
                                User::create([
                                    'name' => $record->full_name,
                                    'email' => $record->email,
                                    'student_id' => $record->student_id,
                                    'role' => 'alumni', 
                                    'is_approved' => true,
                                    'department' => $record->department,
                                    'batch' => $record->batch,
                                    'profile_image' => $record->profile_image ?? '',
                                    'password' => bcrypt('AlumniPhone123@#'), 
                                ]);

                                Notification::make()
                                    ->title('User Created Successfully')
                                    ->body('A new alumni user has been created for ' . $record->full_name)
                                    ->success()
                                    ->send();
                            } else {
                                // Update existing user role to alumni if they were already in the system
                                if ($user->role !== 'admin') { // Don't demote admins
                                    $user->update(['role' => 'alumni']);
                                }

                                Notification::make()
                                    ->title('User Role Updated')
                                    ->body($record->full_name . ' has been updated to Alumni status.')
                                    ->success()
                                    ->send();
                            }
                        }
                    }),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAlumniRegistrations::route('/'),
            'create' => Pages\CreateAlumniRegistration::route('/create'),
            'edit' => Pages\EditAlumniRegistration::route('/{record}/edit'),
        ];
    }
}
