<?php namespace App\Repositories;

use App\LeaveEntitlement;
use Bosnadev\Repositories\Contracts\RepositoryInterface;
use Bosnadev\Repositories\Eloquent\Repository;

class LeaveEntitlementsRepository extends Repository{

  public function model(){
    return LeaveEntitlement;
  }

}?>
