<?php

namespace App\Filament\Resources\NewsArticles;

use App\Filament\Resources\NewsArticles\Pages\CreateNewsArticle;
use App\Filament\Resources\NewsArticles\Pages\EditNewsArticle;
use App\Filament\Resources\NewsArticles\Pages\ListNewsArticles;
use App\Models\NewsArticle;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Size;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class NewsArticleResource extends Resource
{
    protected static ?string $model = NewsArticle::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static ?string $navigationLabel = 'Berita';

    protected static ?string $modelLabel = 'Berita';

    protected static ?string $pluralModelLabel = 'Berita';

    protected static ?string $slug = 'news';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Konten Berita')
                    ->columnSpan(2)
                    ->components([
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, ?string $state, callable $set): void {
                                if ($operation !== 'create') {
                                    return;
                                }

                                $set('slug', Str::slug($state));
                            }),
                        TextInput::make('slug')
                            ->label('Slug URL')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Digunakan pada alamat halaman: /berita/slug-ini'),
                        RichEditor::make('content')
                            ->label('Isi Berita')
                            ->helperText('Kosongkan untuk Berita Nasional yang mengarah ke tautan media eksternal.')
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('news/content'),
                    ]),

                Section::make('Publikasi')
                    ->columnSpan(1)
                    ->components([
                        Select::make('type')
                            ->label('Tampil di')
                            ->options(NewsArticle::typeOptions())
                            ->required(),
                        Select::make('category')
                            ->label('Kategori')
                            ->options(NewsArticle::categoryOptions())
                            ->required()
                            ->default('Berita'),
                        TextInput::make('document_number')
                            ->label('Nomor')
                            ->maxLength(255)
                            ->placeholder('Contoh: 123/BGN/IX/2026'),
                        DatePicker::make('published_at')
                            ->label('Tanggal terbit')
                            ->required()
                            ->default(now()),
                        TextInput::make('source')
                            ->label('Sumber')
                            ->maxLength(120)
                            ->placeholder('Contoh: bgn internal'),
                        TextInput::make('url')
                            ->label('Tautan sumber')
                            ->url()
                            ->maxLength(2048)
                            ->placeholder('https://contoh.go.id/berita')
                            ->helperText('Berita Nasional akan membuka tautan ini langsung di tab baru.'),
                        FileUpload::make('image_path')
                            ->label('Gambar utama')
                            ->image()
                            ->disk('public')
                            ->directory('news')
                            ->maxSize(2048),
                        TextInput::make('sort_order')
                            ->label('Urutan tampil')
                            ->numeric()
                            ->default(0)
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Tampilkan')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Gambar')
                    ->disk('public')
                    ->square(),
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('type')
                    ->label('Tampil di')
                    ->formatStateUsing(fn (string $state): string => NewsArticle::typeOptions()[$state] ?? $state)
                    ->badge(),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('published_at')
                    ->label('Terbit')
                    ->date('d M Y')
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Tampil'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tampil di')
                    ->options(NewsArticle::typeOptions()),
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options(NewsArticle::categoryOptions()),
            ])
            ->defaultSort('published_at', 'desc')
            ->recordActions([
                EditAction::make()
                    ->label('Edit')
                    ->color('primary')
                    ->size(Size::Small),
                DeleteAction::make()
                    ->label('Hapus')
                    ->size(Size::Small),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNewsArticles::route('/'),
            'create' => CreateNewsArticle::route('/create'),
            'edit' => EditNewsArticle::route('/{record}/edit'),
        ];
    }
}
