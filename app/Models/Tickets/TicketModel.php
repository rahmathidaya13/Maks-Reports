<?php

namespace App\Models\Tickets;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TicketModel extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'tickets';
    protected $primaryKey = 'ticket_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'created_by',
        'subject',
        'category',
        'priority',
        'status',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
    public function messages()
    {
        return $this->hasMany(TicketMessageModel::class, 'ticket_id', 'ticket_id');
    }
    public function latestMessage()
    {
        return $this->hasOne(TicketMessageModel::class, 'ticket_id', 'ticket_id')->latestOfMany('ticket_id', 'ticket_id');
    }
}
