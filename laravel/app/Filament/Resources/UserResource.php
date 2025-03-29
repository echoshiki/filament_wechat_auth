<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = '用户管理';

    protected static ?string $label = '用户';

    protected static ?string $slug = 'users';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereDoesntHave('roles');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('用户名')
                    ->maxLength(255)                 
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'unique' => '用户名已存在',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->label('手机号')
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'unique' => '该手机号已存在',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'unique' => '该邮箱已存在',
                    ])
                    ->maxLength(255)
                    ->label('邮箱')
                    ->required(),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->minLength(6)
                    ->required(fn (string $context): bool => $context === 'create')
                    ->confirmed()
                    // 仅在输入后更新密码
                    ->dehydrated(fn ($state) => filled($state))
                    ->label('密码')
                    ->validationMessages([
                        'confirmed' => '两次输入的密码不一致',
                    ]),
                Forms\Components\TextInput::make('password_confirmation')
                    ->password()
                    ->requiredWith('password')
                    ->same('password')
                    ->label('确认密码')
                    // 不保存到数据库
                    ->dehydrated(false)
                    ->validationMessages([
                        'required_with' => '请确认密码'
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->label('用户名'),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->label('手机号'),
                Tables\Columns\TextColumn::make('email')
                    ->label('邮箱'),
                Tables\Columns\TextColumn::make('created_at')
                    ->sortable()
                    ->label('创建时间')
                    ->dateTime(),
            ])
            ->filters([
                //
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
