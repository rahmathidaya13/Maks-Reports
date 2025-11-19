<?php

namespace App\Models;

use Illuminate\Support\Str;
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
    protected $dates = ["deleted_at"];
    protected $fillable = [
        'created_by',
        'report_code',
        'report_date',
        'report_time',
        'count_status',
        'description',
    ];
    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->report_code = Str::random(8);
        });
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
