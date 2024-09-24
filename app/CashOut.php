<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CashOut extends Model
{
    protected $fillable = [ 
        'year', 
        'days', 
        'unit_cost', 
        'leave_type', 
        'applicant_username', 
        'date_applied', 
        'supervisor_username', 
        'supervisor_response', 
        'supervisor_response_date', 
        'md_username', 
        'md_response', 
        'md_response_date', 
        'accounts_username',
        'accounts_response',
        'accounts_response_date',
        'accounts_payment_reference',
        'payment_reference_captured_by',
        'confirmed',
        'date_confirmed'
    ]; 
}
