<?php

namespace Hydrat\GroguCMS\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Hydrat\GroguCMS\Actions\Seo\GenerateSeoScore;
use Hydrat\GroguCMS\Filament\Concerns as Parts;
use Hydrat\GroguCMS\Filament\Concerns\InteractsWithBlueprint;
use Hydrat\GroguCMS\Filament\Contracts\HasBlueprint;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use RalphJSmit\Filament\MediaLibrary\Tables\Columns\MediaColumn;

abstract class TabbedCmsResource extends Resource implements HasBlueprint
{
    use InteractsWithBlueprint;
    use Parts\HasContentTab;
    use Parts\HasOverviewTab;
    use Parts\HasSeoTab;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make()
                    ->contained(false)
                    ->persistTab(true)
                    ->id(str(static::class)->afterLast('\\')->append('CmsTabs')->kebab())
                    ->columnSpanFull()
                    ->schema([
                        ...static::getOverviewTabSchema($form),
                        ...static::getContentTabSchema($form),
                        ...static::getSeoTabSchema($form),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ...static::getTableColumns(),
            ])
            ->actions([
                Tables\Actions\Action::make('visit')
                    ->iconSoftButton('heroicon-o-arrow-up-right')
                    ->url(fn (Model $record) => optional($record->blueprint())->frontUrl())
                    ->openUrlInNewTab(),

                Tables\Actions\EditAction::make()->iconSoftButton('heroicon-o-pencil-square'),
                Tables\Actions\DeleteAction::make()->iconSoftButton('heroicon-o-trash'),
            ]);
    }

    protected static function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('id')
                ->numeric()
                ->label('ID')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true)
                ->weight('bold'),

            Tables\Columns\TextColumn::make('title')
                ->sortable()
                ->searchable()
                ->description(fn (Model $record) => optional($record->blueprint())->frontUri()),

            Tables\Columns\TextColumn::make('published_at')
                ->label('Status')
                ->sortable()
                ->badge()
                ->getStateUsing(function (Model $record) {
                    return match ($record->published_at) {
                        null => __('Draft'),
                        default => __('Published at').' '.$record->published_at->format('d/m/Y'),
                    };
                })
                ->color(fn (Model $record) => $record->published_at ? 'success' : 'warning')
                ->toggleable(isToggledHiddenByDefault: false),

            Tables\Columns\TextColumn::make('user.name')
                ->label('Author')
                ->searchable()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: false),

            Tables\Columns\TextColumn::make('seo_score')
                ->label('SEO')
                ->badge()
                ->getStateUsing(fn (Model $record) => Cache::get(GenerateSeoScore::getCacheKey($record))?->getScore())
                ->formatStateUsing(fn (string|Htmlable|null $state) => $state ? $state.'/100' : '')
                ->color(fn (string|Htmlable|null $state) => match (true) {
                    $state < 50 => 'danger',
                    $state < 85 => 'warning',
                    default => 'success',
                })
                ->toggleable(isToggledHiddenByDefault: false),

            // Tables\Columns\SpatieMediaLibraryImageColumn::make('image')
            //     ->collection('image')
            //     ->conversion('small')
            //     ->disk('media-library')
            //     ->circular(true)
            //     ->toggleable(isToggledHiddenByDefault: false),

            MediaColumn::make('thumbnail')
                ->circular()
                ->size(40),
        ];
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'slug', 'user.name', 'content', 'excerpt'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        return $record->title;
    }

    // public static function getGlobalSearchResultDetails(Model $page) : array
    // {
    //     return [
    //         __('Author') => $page->user?->name,
    //     ];
    // }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['user:id,name']);
    }
}
