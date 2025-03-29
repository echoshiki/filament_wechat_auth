<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdminUserResource\Pages;
use App\Filament\Resources\AdminUserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;


class AdminUserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = '用户管理';

    protected static ?string $label = '管理员';

    protected static ?string $slug = 'admin-users';

    // 只显示拥有角色的用户，即后台管理用户
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('roles')
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', '超级管理员');
            });   
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
                Forms\Components\Select::make('roles')
                    ->multiple()
                    ->preload()
                    ->relationship('roles', 'name', fn ($query) => 
                        $query->where('name', '!=', '超级管理员')
                    )
                    ->label('角色')
                    ->columnSpanFull()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->label('用户名'),
                Tables\Columns\TextColumn::make('email')
                    ->label('邮箱'),
                Tables\Columns\TextColumn::make('roles.name')
                    ->badge()
                    ->label('角色') ,
                Tables\Columns\TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles.name')
                    ->multiple()
                    ->preload()
                    // 只显示除超级管理员外的角色
                    ->relationship('roles', 'name', fn ($query) => 
                        $query->where('name', '!=', '超级管理员')
                    )
                    ->label('角色')
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('编辑'),
                Tables\Actions\DeleteAction::make()->label('删除'),
            ])
            // 批量行为
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
            'index' => Pages\ListAdminUsers::route('/'),
            'create' => Pages\CreateAdminUser::route('/create'),
            'edit' => Pages\EditAdminUser::route('/{record}/edit'),
        ];
    }
}
