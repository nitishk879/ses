<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectFeature extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "recruitment_target_1",
        "recruitment_target_2",
        "recruitment_target_3",
        "recruitment_target_4",
        "recruitment_target_5",
        "recruitment_target_6",
        "case_feature_1",
        "case_feature_2",
        "case_feature_3",
        "case_feature_4",
        "case_feature_5",
        "case_feature_6",
        "case_feature_7",
        "case_feature_8",
        "case_feature_9",
        "case_feature_10",
        "case_feature_11",
        "case_feature_12",
        "case_feature_13",
        "case_feature_14",
        "case_feature_15",
        "project_id"
    ];

    /**
     * Features belongs to project
     *
     * @return BelongsTo
    */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
