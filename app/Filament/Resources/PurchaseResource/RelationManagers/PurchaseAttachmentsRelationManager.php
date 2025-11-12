<?php

namespace App\Filament\Resources\PurchaseResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage; // ✅ Importar Storage

class PurchaseAttachmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'attachments';
    protected static ?string $title = 'Documentos / Anexos';
    protected static ?string $icon = 'heroicon-o-paper-clip';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('file_name')
                    ->label('Nome do Documento')
                    ->required()
                    ->maxLength(255),
                    
                Forms\Components\FileUpload::make('file_path')
                    ->label('Arquivo')
                    ->directory('purchase-attachments')
                    ->disk('public') // Ser explícito sobre o disco
                    ->preserveFilenames()
                    ->storeFileNamesIn('file_name')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('file_name')
            ->columns([
                Tables\Columns\TextColumn::make('file_name')
                    ->label('Documento')
                    ->searchable()
                    ->icon('heroicon-o-document-text')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data de Envio')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Novo Anexo'),
            ])
            ->actions([
                // ✅ AÇÃO DE DOWNLOAD CORRIGIDA
                Tables\Actions\Action::make('download')
                    ->label('Baixar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($record) {
                        // Força o download usando o disco 'public'
                        return Storage::disk('public')->download($record->file_path, $record->file_name);
                    }),

                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\DeleteAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}