<?php

namespace Hydrat\GroguCMS\Models;

use Nevadskiy\Tree\AsTree;
use Spatie\EloquentSortable\Sortable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Spatie\EloquentSortable\SortableTrait;
use Illuminate\Database\Eloquent\Relations;
use Nevadskiy\Tree\Collections\NodeCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Hydrat\GroguCMS\Models\Contracts\Resourceable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MenuItem extends Model implements Resourceable, Sortable
{
    use HasFactory;
    use SortableTrait;
    use AsTree {
        hasCircularReference as hasCircularReferenceTrait;
        joinAncestors as joinAncestorsTrait;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'menu_id',
        'title',
        'parent_id',
        'path',
        'linkeable_id',
        'linkeable_type',
        'url',
        'external',
        'new_tab',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'url',
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'linkeable',
        'children',
    ];

    public function toResource(): JsonResource
    {
        return new \Hydrat\GroguCMS\Http\Resources\MenuItemResource($this);
    }

    public function buildSortQuery()
    {
        return static::query()
            ->when($this->parent_id, fn ($query) => $query->where('parent_id', $this->parent_id))
            ->when(!$this->parent_id, fn ($query) => $query->whereNull('parent_id'));
    }

    protected function hasCircularReference(): bool
    {
        $this->loadMissing('parent');

        return $this->hasCircularReferenceTrait();
    }

    public function joinAncestors(): NodeCollection
    {
        $this->loadMissing('ancestors');

        return $this->joinAncestorsTrait();
    }

    public function menu(): Relations\BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function linkeable(): Relations\MorphTo
    {
        return $this->morphTo();
    }

    public function url(): Attribute
    {
        return new Attribute(
            get: fn ($value, $attributes) => $attributes['linkeable_type'] && $attributes['linkeable_type'] !== 'url'
                ? $this->linkeable?->url
                : $attributes['url'],
            set: fn ($value) => $value
        );
    }
}
