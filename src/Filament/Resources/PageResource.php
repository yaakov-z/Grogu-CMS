<?php

namespace Hydrat\GroguCMS\Filament\Resources;

use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\Page as FilamentPage;
use Hydrat\GroguCMS\Filament\Resources\CmsResource;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Hydrat\GroguCMS\Filament\Resources\PageResource\Pages;

class PageResource extends CmsResource
{
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getModel(): string
    {
        return config('grogu-cms.models.page') ?? \Hydrat\GroguCMS\Models\Page::class;
    }

    public static function getLabel(): string
    {
        return __('Page');
    }

    public static function getPluralLabel(): string
    {
        return __('Pages');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Site');
    }

    public static function form(Form $form): Form
    {
        return parent::form($form);
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    protected static function getTableColumns(): array
    {
        return parent::getTableColumns();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
            'content' => Pages\EditPageContent::route('/{record}/content'),
            'seo' => Pages\EditPageSeo::route('/{record}/seo'),
        ];
    }

    public static function getRecordSubNavigation(FilamentPage $page): array
    {
        return $page->generateNavigationItems([
            Pages\EditPage::class,
            Pages\EditPageContent::class,
            Pages\EditPageSeo::class,
        ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
