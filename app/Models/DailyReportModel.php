<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DailyReportModel extends Model
{
    use HasFactory, HasUuids, SoftDeletes;
    protected $table = 'daily_report';
    protected $primaryKey = 'daily_report_id';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $dates = ["deleted_at"];
    protected $fillable = [
        'created_by',
        'date',
        'leads',
        'closing',
        'fu_yesterday',
        'fu_yesterday_closing',
        'fu_before_yesterday',
        'fu_before_yesterday_closing',
        'fu_last_week',
        'fu_last_week_closing',
        'engage_old_customer',
        'engage_closing',
    ];
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
