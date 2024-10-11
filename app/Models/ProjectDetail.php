<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "data_category",
        "data_medium_section",
        "data_sub_section",
        "remarks",
        "text_data",
        "numerical_data",
        "date_data",
        "project_id"
    ];

    /**
     * A project detail belongs to the project
     *
     * @return BelongsTo
    */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
