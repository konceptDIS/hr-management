<?php namespace App\Repositories;

use App\LeaveType;
use Bosnadev\Repositories\Contracts\RepositoryInterface;
use Bosnadev\Repositories\Eloquent\Repository;

class LeaveTypesRepository extends Repository{

  public function model(){
    return LeaveType;
  }
}?>
