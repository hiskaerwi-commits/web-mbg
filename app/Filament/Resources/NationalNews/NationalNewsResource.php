<?php

namespace App\Filament\Resources\NationalNews;

use App\Filament\Resources\NationalNews\Pages\CreateNationalNews;
use App\Filament\Resources\NationalNews\Pages\EditNationalNews;
use App\Filament\Resources\NationalNews\Pages\ListNationalNews;
use App\Models\NewsArticle;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Size;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NationalNewsResource extends Resource
{
    protected static ?string $model = NewsArticle::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static ?string $navigationLabel = 'Berita Nasional';

    protected static ?string $modelLabel = 'Berita Nasional';

    protected static ?string $pluralModelLabel = 'Berita Nasional';

    protected static ?string $slug = 'national-news';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('type', NewsArticle::TYPE_NASIONAL);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Berita dari Media Eksternal')
                    ->description('Kartu akan membuka tautan media resmi di tab baru.')
                    ->columnSpanFull()
                    ->columns(2)
                    ->components([
                        TextInput::make('title')->label('Judul berita')->required()->maxLength(255)->columnSpanFull(),
                        TextInput::make('source')->label('Sumber media')->placeholder('Contoh: ANTARA, VIVA, Suara.com')->required()->maxLength(120),
                        DatePicker::make('published_at')->label('Tanggal terbit')->required()->default(now()),
                        TextInput::make('url')->label('Tautan berita resmi')->url()->required()->maxLength(2048)->columnSpanFull(),
                        FileUpload::make('image_path')->label('Thumbnail')->image()->required()->disk('public')->directory('news/national')->maxSize(2048)->columnSpanFull(),
                        TextInput::make('sort_order')->label('Urutan jika tanggal sama')->numeric()->default(0)->required(),
                        Toggle::make('is_active')->label('Tampilkan di beranda')->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')->label('Thumbnail')->disk('public')->square(),
                TextColumn::make('title')->label('Judul')->searchable()->limit(55),
                TextColumn::make('source')->label('Sumber')->badge()->color('gray'),
                TextColumn::make('published_at')->label('Terbit')->date('d M Y')->sortable(),
                ToggleColumn::make('is_active')->label('Tampil'),
            ])
            ->defaultSort('published_at', 'desc')
            ->recordActions([
                EditAction::make()->label('Edit')->color('primary')->size(Size::Small),
                DeleteAction::make()->label('Hapus')->size(Size::Small),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNationalNews::route('/'),
            'create' => CreateNationalNews::route('/create'),
            'edit' => EditNationalNews::route('/{record}/edit'),
        ];
    }
}
