<?php

namespace App\Models\Tickets;

use Illuminate\Foundation\Auth\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TicketMessageModel extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'ticket_messages';
    protected $primaryKey = 'message_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'created_by',
        'ticket_id',
        'message',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function ticket()
    {
        return $this->belongsTo(TicketModel::class, 'ticket_id', 'ticket_id');
    }
}
