<?php
namespace AdminGen\Models;

use PhalconX\Mvc\Model;

class Customer extends Model
{
   /**
    * @var integer
    */
   public $id;

   /**
    * @var string
    */
   public $name;

   /**
    * @var string
    */
   public $email;

   /**
    * @var string
    */
   public $mobile;

   /**
    * @var string
    */
   public $memo;

   public function getSource()
   {
       return "customers";
   }
}
