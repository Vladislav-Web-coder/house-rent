<?php

namespace App\Filament\Pages;

use App\Services\ContractFileProvider;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

class ContractManagement extends Page implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Договор аренды';
    protected static ?string $title = 'Управление договором аренды';
    protected static ?string $navigationGroup = 'Контент';
    protected static ?int $navigationSort = 50;
    protected static string $view = 'filament.pages.contract-management';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('contract_file')
                    ->label('Файл договора (PDF)')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(10240) // 10 МБ
                    ->required()
                    ->disk('local')
                    ->directory('contracts-temp')
                    ->helperText('Этот файл будет автоматически прикрепляться к письмам клиентам при создании заявки'),
            ])
            ->statePath('data');
    }

    /**
     * Кнопки действий в хедере страницы
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('upload')
                ->label('Загрузить договор')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->form([
                    FileUpload::make('contract_file')
                        ->label('Файл договора (PDF)')
                        ->acceptedFileTypes(['application/pdf'])
                        ->maxSize(10240)
                        ->required()
                        ->disk('local')
                        ->directory('contracts-temp')
                        ->helperText('Новый файл заменит текущий договор'),
                ])
                ->action(function (array $data): void {
                    $this->saveContract($data);
                }),

            Action::make('download')
                ->label('Скачать текущий')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->visible(app(ContractFileProvider::class)->exists())
                ->url(fn () => route('admin.contract.download'))
                ->openUrlInNewTab(),
        ];
    }

    /**
     * Сохраняет загруженный договор
     */
    private function saveContract(array $data): void
    {
        try {
            $tempPath = $data['contract_file'];
            $targetPath = 'contracts/rental-agreement.pdf';

            // Создаём директорию если её нет
            if (!Storage::disk('local')->exists('contracts')) {
                Storage::disk('local')->makeDirectory('contracts');
            }

            // Удаляем старый файл
            if (Storage::disk('local')->exists($targetPath)) {
                Storage::disk('local')->delete($targetPath);
            }

            // Копируем новый файл
            $content = Storage::disk('local')->get($tempPath);
            Storage::disk('local')->put($targetPath, $content);

            // Удаляем временный файл
            Storage::disk('local')->delete($tempPath);

            Notification::make()
                ->title('Договор успешно обновлён')
                ->body('Новый файл будет использоваться для всех следующих заявок.')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Ошибка загрузки')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
