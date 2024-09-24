<?php

namespace App;

class LeaveTypeViewModel{
    public $name;
    public $id;
    public $balance;

    public function requireDocument(){
        if($this->name == 'Examination'){ //$this->name == 'Sick' or $this->name == 'Paternity' or $this->name == 'Maternity' or 
          return true;
        }
        return false;
      }
  
      public function requireBalance(){
        if($this->name == 'Sick' or $this->name == 'Examination'){
          return false;
        }
        return true;
      }
      
      public function enabled(){
          if($this->name == 'Casual'){
            return false;
          }
          else if($this->name == 'Examination' or $this->name == 'Sick'){
            return true;
          }
          else{
            return $this->balance > 0;
          }
      }
}