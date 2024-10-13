<?php

namespace Hydrat\GroguCMS\Models;

use Spatie\EloquentSortable\Sortable;
use Illuminate\Database\Eloquent\Model;
use Hydrat\GroguCMS\Enums\FormFieldType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use Spatie\EloquentSortable\SortableTrait;

class FormField extends Model implements Sortable
{
    use HasFactory;
    use SortableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'form_id',
        'name',
        'content',
        'type',
        'options',
        'column_span',
        'order',
        'placeholder',
        'helper_text',
        'required',
        'rows',
        'multiple',
        'min',
        'max',
        'min_date',
        'max_date',
    ];

    protected $casts = [
        'options' => 'collection',
        'rules' => 'collection',
        'required' => 'boolean',
        'multiple' => 'boolean',
        'type' => FormFieldType::class,
    ];

    public $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];

    public function form(): Relations\BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function buildSortQuery()
    {
        return static::query()->where('survey_id', $this->survey_id);
    }

    public function getKeyAttribute()
    {
        return "q{$this->id}";
    }
}
