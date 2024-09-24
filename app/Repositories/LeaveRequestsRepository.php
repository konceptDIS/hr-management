<?php 

namespace App\Repositories;

use App;
use Bosnadev\Repositories\Contracts\RepositoryInterface;
use Bosnadev\Repositories\Eloquent\Repository;

class LeaveRequestsRepository extends Repository{

  public function model(){
    return 'App\LeaveRequest';
  }

  /**
  * @param $userId
  * @param $date
  * @return mixed
  */
  public function getByUserForDate($userId, $date){
    return $this->model->where('submitter_user_id', '=', $userId);
  }

  /**
  * @param $userId
  * @param $date
  * @param $type e.g. Incoming
  * @param $closed
  * @return mixed
  */
  public function getPendingFirstApproval($userId){
    return $this->model->where(
      [
        ['submitter_user_id', '=', $userId],
        ['$type', '=', $type],
        ['closed', '=', $closed]
      ])->whereDate('date', $date);
  }

  /**
  * @param $userId
  * @param $date
  * @param $type e.g. Incoming
  * @param $closed
  * @return mixed
  */
  public function getByUserForUserByDate($byUserId, $forUserId, $type, $date, $closed=false){
    //select * from LeaveRequest where taker_user_id==byUserId
    //and for_next_action_by_user_id==forUserId
    //and date = date
    //and closed = closed
    //and type==$type
    return $this->model->where(
      [
        ['taker_user_id', '=', $userId],
        ['for_next_action_by_user_id', '=', $forUserId],
        ['type', '=', $type],
        ['closed', '=', $closed]
      ])->whereDate('date', $date);
  }

  public function viewed($actionId){
    //get the action
    //set viewed = true
    //set date
    //save changes
    $this->update('true', $actionId);
  }


  public function closed($actionId){
    //get the action

    //set closed = true

    //set date

    //save changes
  }
}?>
