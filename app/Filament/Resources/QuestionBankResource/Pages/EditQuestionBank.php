<?php

namespace App\Filament\Resources\QuestionBankResource\Pages;

use App\Filament\Resources\QuestionBankResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQuestionBank extends EditRecord
{
    protected static string $resource = QuestionBankResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('autoFillAI')
                ->label('✨ Auto-Fill with AI')
                ->color('info')
                ->action(function () {
                    \App\Jobs\GenerateQuestionBankMetadata::dispatchSync($this->record);
                    $this->fillForm();
                    \Filament\Notifications\Notification::make()
                        ->title('AI Auto-Fill Complete')
                        ->success()
                        ->send();
                }),
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        // If the admin just approved it, trigger the AI auto-fill
        if ($this->record->wasChanged('status') && $this->record->status === 'approved') {
            \App\Jobs\GenerateQuestionBankMetadata::dispatchSync($this->record);
        }
    }
}
