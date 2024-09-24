<?php 

namespace App\Repositories;

use App\Office;
use Bosnadev\Repositories\Contracts\RepositoryInterface;
use Bosnadev\Repositories\Eloquent\Repository;

class OfficesRepository extends Repository{

  public function model(){
    return 'App\Office';
  }
}?>
