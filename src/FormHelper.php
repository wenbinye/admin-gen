<?php
namespace AdminGen;

use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\TextArea;

class FormHelper
{
    public static function process($form)
    {
        foreach ($form as $elem) {
            if ($elem instanceof Hidden) {
                $elem->setAttribute('hidden', true);
            } elseif ($elem instanceof TextArea) {
                $elem->setAttribute('class', 'form-control textarea');
            } else {
                $elem->setAttribute('class', 'form-control');
            }
            $label = $elem->getLabel();
            if (!$label) {
                $label = ucfirst($elem->getName());
            }
            $elem->setLabel($label);
        }
    }

    public static function hasTextarea($form)
    {
        foreach ($form as $elem) {
            if ($elem instanceof TextArea) {
                return true;
            }
        }
        return false;
    }
    
    public static function getColumns($form)
    {
        $columns = [];
        foreach ($form as $elem) {
            if ($elem->getAttribute('hidden')) {
                continue;
            }
            $columns[] = (object) [
                'name' => $elem->getName(),
                'label' => $elem->getLabel()
            ];
        }
        return $columns;
    }
}
