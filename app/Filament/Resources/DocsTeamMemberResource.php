<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocsTeamMemberResource\Pages;
use App\Models\DocsTeamMember;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DocsTeamMemberResource extends Resource
{
    protected static ?string $model = DocsTeamMember::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Documentation';

    protected static ?string $navigationLabel = 'Docs Team';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Member Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('role')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('github_url')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\FileUpload::make('profile_image')
                            ->image()
                            ->directory('docs/team')
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeTargetWidth('400')
                            ->imageResizeTargetHeight('400'),
                        Forms\Components\Textarea::make('bio')
                            ->rows(3),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable(),
                Tables\Columns\ImageColumn::make('profile_image')
                    ->label('Photo')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role'),
                Tables\Columns\TextColumn::make('email'),
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
            'index' => Pages\ListDocsTeamMembers::route('/'),
            'create' => Pages\CreateDocsTeamMember::route('/create'),
            'edit' => Pages\EditDocsTeamMember::route('/{record}/edit'),
        ];
    }
}
