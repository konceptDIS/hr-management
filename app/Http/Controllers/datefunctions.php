<?php

function setDate($input, $separator = '/'){

      $parts = explode($separator, $input);
      // dd($parts);
      if($parts!=null && sizeof($parts)==3){
        return Carbon\Carbon::create($parts[2],$parts[1],$parts[0]);
      }
      return null;
  }

    function parseDate($input){
      return Carbon\Carbon::parse($input);
    }
?>
