<?php
namespace AdminGen\Forms;

use PhalconX\Mvc\SimpleModel;

class Customer extends SimpleModel
{
   /**
    * @Hidden
    * @Valid(type=integer)
    */
   public $id;

   /**
    * @Text(label="User's name")
    * @Valid(required=true, type=string, maxLength=100)
    */
   public $name;

   /**
    * @Text
    * @Valid(required=true, type=string, maxLength=100)
    */
   public $email;

   /**
    * @Text
    * @Valid(required=true, type=string, maxLength=100)
    */
   public $mobile;

   /**
    * @TextArea
    * @Valid(type=string, maxLength=1024)
    */
   public $memo;
}
