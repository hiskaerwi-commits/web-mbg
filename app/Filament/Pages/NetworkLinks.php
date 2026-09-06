<?php

namespace App\Filament\Pages;

use App\Models\NetworkLink;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Livewire\WithPagination;

class NetworkLinks extends Page
{
    use WithPagination;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected string $view = 'filament.pages.network-links';

    public ?int $editingId = null;

    public string $name = '';

    public string $url = '';

    public int $sortOrder = 0;

    public bool $isActive = true;

    public static function getNavigationLabel(): string
    {
        return 'Jaringan Wilayah';
    }

    public function getTitle(): string
    {
        return 'Jaringan Wilayah';
    }

    public function getLinksProperty()
    {
        return NetworkLink::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(10);
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:160'],
            'url' => ['required', 'url', 'max:2048'],
            'sortOrder' => ['required', 'integer', 'min:0'],
            'isActive' => ['boolean'],
        ]);

        $link = $this->editingId ? NetworkLink::query()->findOrFail($this->editingId) : new NetworkLink();

        $link->fill([
            'name' => $data['name'],
            'url' => $data['url'],
            'sort_order' => $data['sortOrder'],
            'is_active' => $data['isActive'],
        ])->save();

        $wasEditing = $this->editingId !== null;

        $this->resetForm();
        $this->resetPage();

        Notification::make()
            ->title($wasEditing ? 'Jaringan berhasil diperbarui' : 'Jaringan berhasil ditambahkan')
            ->success()
            ->send();
    }

    public function edit(int $linkId): void
    {
        $link = NetworkLink::query()->findOrFail($linkId);

        $this->editingId = $link->id;
        $this->name = $link->name;
        $this->url = $link->url;
        $this->sortOrder = $link->sort_order;
        $this->isActive = $link->is_active;
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function toggle(int $linkId): void
    {
        $link = NetworkLink::query()->findOrFail($linkId);
        $link->update(['is_active' => ! $link->is_active]);
    }

    public function delete(int $linkId): void
    {
        NetworkLink::query()->findOrFail($linkId)->delete();

        if ($this->editingId === $linkId) {
            $this->resetForm();
        }

        Notification::make()->title('Jaringan dihapus')->success()->send();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->url = '';
        $this->sortOrder = 0;
        $this->isActive = true;
        $this->resetValidation();
    }
}
