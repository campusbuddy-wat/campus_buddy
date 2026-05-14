<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DistrictAssociationResource\Pages;
use App\Filament\Resources\DistrictAssociationResource\RelationManagers;
use App\Models\DistrictAssociation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DistrictAssociationResource extends Resource
{
    protected static ?string $model = DistrictAssociation::class;

    protected static ?string $navigationGroup = 'Community Setting';

    public static function form(Form $form): Form
    {
        $districtsByDivision = [
            'Dhaka' => ['Dhaka' => 'Dhaka', 'Gazipur' => 'Gazipur', 'Kishoreganj' => 'Kishoreganj', 'Manikganj' => 'Manikganj', 'Munshiganj' => 'Munshiganj', 'Narayanganj' => 'Narayanganj', 'Narsingdi' => 'Narsingdi', 'Tangail' => 'Tangail', 'Faridpur' => 'Faridpur', 'Gopalganj' => 'Gopalganj', 'Madaripur' => 'Madaripur', 'Rajbari' => 'Rajbari', 'Shariatpur' => 'Shariatpur'],
            'Chattogram' => ['Chattogram' => 'Chattogram', 'Cox\'s Bazar' => 'Cox\'s Bazar', 'Rangamati' => 'Rangamati', 'Bandarban' => 'Bandarban', 'Khagrachhari' => 'Khagrachhari', 'Feni' => 'Feni', 'Lakshmipur' => 'Lakshmipur', 'Noakhali' => 'Noakhali', 'Brahmanbaria' => 'Brahmanbaria', 'Cumilla' => 'Cumilla', 'Chandpur' => 'Chandpur'],
            'Rajshahi' => ['Rajshahi' => 'Rajshahi', 'Sirajganj' => 'Sirajganj', 'Pabna' => 'Pabna', 'Bogura' => 'Bogura', 'Chapai Nawabganj' => 'Chapai Nawabganj', 'Naogaon' => 'Naogaon', 'Joypurhat' => 'Joypurhat', 'Natore' => 'Natore'],
            'Khulna' => ['Khulna' => 'Khulna', 'Jashore' => 'Jashore', 'Satkhira' => 'Satkhira', 'Meherpur' => 'Meherpur', 'Narail' => 'Narail', 'Chuadanga' => 'Chuadanga', 'Kushtia' => 'Kushtia', 'Magura' => 'Magura', 'Bagerhat' => 'Bagerhat', 'Jhenaidah' => 'Jhenaidah'],
            'Barishal' => ['Barishal' => 'Barishal', 'Patuakhali' => 'Patuakhali', 'Bhola' => 'Bhola', 'Pirojpur' => 'Pirojpur', 'Barguna' => 'Barguna', 'Jhalokati' => 'Jhalokati'],
            'Sylhet' => ['Sylhet' => 'Sylhet', 'Moulvibazar' => 'Moulvibazar', 'Habiganj' => 'Habiganj', 'Sunamganj' => 'Sunamganj'],
            'Rangpur' => ['Rangpur' => 'Rangpur', 'Panchagarh' => 'Panchagarh', 'Dinajpur' => 'Dinajpur', 'Lalmonirhat' => 'Lalmonirhat', 'Nilphamari' => 'Nilphamari', 'Kurigram' => 'Kurigram', 'Thakurgaon' => 'Thakurgaon', 'Gaibandha' => 'Gaibandha'],
            'Mymensingh' => ['Mymensingh' => 'Mymensingh', 'Jamalpur' => 'Jamalpur', 'Netrokona' => 'Netrokona', 'Sherpur' => 'Sherpur'],
        ];

        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('division')
                    ->options([
                        'Dhaka' => 'Dhaka',
                        'Chattogram' => 'Chattogram',
                        'Rajshahi' => 'Rajshahi',
                        'Khulna' => 'Khulna',
                        'Barishal' => 'Barishal',
                        'Sylhet' => 'Sylhet',
                        'Rangpur' => 'Rangpur',
                        'Mymensingh' => 'Mymensingh',
                    ])
                    ->required()
                    ->live()
                    ->searchable()
                    ->afterStateUpdated(fn (Forms\Set $set) => $set('district', null)),
                Forms\Components\Select::make('district')
                    ->options(function (Forms\Get $get) use ($districtsByDivision) {
                        $division = $get('division');
                        if (!$division) {
                            return [];
                        }
                        return $districtsByDivision[$division] ?? [];
                    })
                    ->required()
                    ->searchable()
                    ->label('District'),
                Forms\Components\FileUpload::make('image')
                    ->id('logo_image_field')
                    ->image()
                    ->disk('public')
                    ->directory('community/district_associations/logos')
                    ->label('Logo Image'),
                Forms\Components\TextInput::make('link')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('members_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\FileUpload::make('cover_image')
                    ->id('hero_image_field')
                    ->image()
                    ->disk('public')
                    ->directory('community/district_associations/covers')
                    ->label('Hero Image (Blue Portion)'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('division')
                    ->badge()
                    ->color('primary')
                    ->searchable(),
                Tables\Columns\TextColumn::make('district')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('image')
                    ->label('Logo'),
                Tables\Columns\ImageColumn::make('cover_image')->label('Hero Image'),
                Tables\Columns\TextColumn::make('members_count')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('division')
                    ->options([
                        'Dhaka' => 'Dhaka',
                        'Chattogram' => 'Chattogram',
                        'Rajshahi' => 'Rajshahi',
                        'Khulna' => 'Khulna',
                        'Barishal' => 'Barishal',
                        'Sylhet' => 'Sylhet',
                        'Rangpur' => 'Rangpur',
                        'Mymensingh' => 'Mymensingh',
                    ]),
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
            'index' => Pages\ListDistrictAssociations::route('/'),
            'create' => Pages\CreateDistrictAssociation::route('/create'),
            'edit' => Pages\EditDistrictAssociation::route('/{record}/edit'),
        ];
    }
}
