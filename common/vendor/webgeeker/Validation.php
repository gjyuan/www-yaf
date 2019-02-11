<?php
namespace vendor\webgeeker;
use vendor\composer\Base;

class Validation extends Base {
    public static function validate($params, $validations, $ignoreRequired = false){
        try{
            return \WebGeeker\Validation\Validation::validate($params,$validations,$ignoreRequired);
        }catch (\Exception $e ){
            throw new \Exception("Param is invalidate".$e->getMessage());
        }
    }
}