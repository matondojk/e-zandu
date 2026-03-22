<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SavedReportResource\Pages;
use App\Models\SavedReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use App\Services\SubscriptionManager;

class SavedReportResource extends Resource
{
    protected static ?string $model = SavedReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-bookmark-square';
    protected static ?string $navigationLabel = 'Relatórios Guardados';
    protected static ?string $modelLabel = 'Relatório Guardado';
    protected static ?string $pluralModelLabel = 'Relatórios Guardados';
    protected static ?string $navigationGroup = 'Recursos de IA';
    protected static ?string $slug = 'relatorios-guardados';

    // --- 2. ADICIONAR OS MÉTODOS DE BADGE ---

    public static function getNavigationBadge(): ?string
    {
        $manager = app(SubscriptionManager::class);

        if (!$manager->canUse('analise-inteligente')) {
            return 'ESSENCIAL';
        }

        return null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        if (!app(SubscriptionManager::class)->canUse('analise-inteligente')) {
            return 'warning';
        }

        return null;
    }

    // --- FIM DA ADIÇÃO ---

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nome do Relatório')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('prompt')
                    ->label('Comando ou SQL')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),

                Forms\Components\Section::make('Agendamento (Opcional)')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Toggle::make('is_scheduled')
                            ->label('Ativar Agendamento de E-mail')
                            ->inline(false)
                            ->reactive(),
                        Forms\Components\Select::make('frequency')
                            ->label('Frequência')
                            ->options([
                                'daily' => 'Diário',
                                'weekly' => 'Semanal (à Segunda-feira)',
                                'monthly' => 'Mensal (dia 1)',
                            ])
                            ->required(fn(Forms\Get $get) => $get('is_scheduled'))
                            ->visible(fn(Forms\Get $get) => $get('is_scheduled')),
                        Forms\Components\TextInput::make('recipient_email')
                            ->label('E-mail do Destinatário')
                            ->email()
                            ->required(fn(Forms\Get $get) => $get('is_scheduled'))
                            ->visible(fn(Forms\Get $get) => $get('is_scheduled'))
                            ->helperText('O relatório em PDF será enviado para este e-mail.'),
                    ])->columnSpanFull(),

                Forms\Components\Hidden::make('user_id')
                    ->default(Auth::id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('prompt')->limit(50),
                Tables\Columns\IconColumn::make('is_scheduled')
                    ->label('Agendado')
                    ->boolean(),
                Tables\Columns\TextColumn::make('frequency')->label('Frequência'),
                Tables\Columns\TextColumn::make('recipient_email')->label('Destinatário'),
            ])
            ->actions([

                Tables\Actions\DeleteAction::make()
                    ->iconButton() 
                    ->icon('heroicon-o-trash'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    // Filtrar para mostrar apenas os relatórios do utilizador logado
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::id());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSavedReports::route('/'),
        ];
    }
}
