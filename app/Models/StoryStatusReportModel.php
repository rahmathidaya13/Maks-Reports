<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StoryStatusReportModel extends Model
{
    use HasFactory, HasUuids, SoftDeletes;
    protected $table = 'story_status_reports';
    protected $primaryKey = 'story_status_id';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $dates    = ["deleted_at"];
    protected $fillable = [
        'created_by',
        'report_date',
        'report_time',
        'count_status',
        'description',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
