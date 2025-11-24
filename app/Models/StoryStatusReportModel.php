<?php

namespace App\Models;

use Illuminate\Support\Carbon;
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
    ];
    protected $appends = ['informasi'];
    public function getInformasiAttribute()
    {
        $created = Carbon::parse($this->created_at);
        $updated = Carbon::parse($this->updated_at);
        // Jika lebih dari 1 hari → tidak perlu highlight
        if ($created->diffInDays(now()) >= 1 && $updated->diffInDays(now()) >= 1) {
            return "";
        }
        if ($created->eq($updated)) {
            return "Baru dibuat " . $created->diffForHumans();
        }
        return "Terakhir diperbarui " . $updated->diffForHumans();
    }
    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->report_code = str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);
            ;
        });
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
