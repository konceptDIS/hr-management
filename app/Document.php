<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $primaryKey = 'id';

    protected $table = 'documents';

    protected $fillable = [
        'id',
        'leave_request_id',
        'description',
        'ext',
        'uploaded_by',
        'sn',
        'filename',
        'size',
        'type'
    ];

    /**
     * Get the case this belongs to.
     */
    public function suit()
    {
        return $this->belongsTo('App\Suit');
    }
}
