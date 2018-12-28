<?php
/**
 * Created by PhpStorm.
 * User: zgzheng
 * Date: 2018/12/26
 * Time: 9:25 PM
 */

//base
define('IOSJsonKey_class_name', 'class_name');
define('IOSJsonKey_instance_name', 'instance_name');
define('IOSJsonKey_ivar_propertys', 'ivar_propertys');
define('IOSJsonKey_view_subviews', 'view_subviews');
define('IOSJsonKey_view_constraints', 'view_constraints');


//UIView
define('IOSProperty_BackgroundColor', 'UIColor_backgroundColor');
define('IOSProperty_alpha', 'CGFloat_alpha');

//define('IOSProperty_', 'hidden');
//define('IOSProperty_', 'contentMode');
//define('IOSProperty_', 'alpha');
//define('IOSProperty_cornerRadius', 'cornerRadius');
//define('IOSProperty_', 'borderWidth');
//define('IOSProperty_', 'borderColor');
//
//
//UIImageView
define('IOSProperty_image', 'image');
//
//UIButton
define('IOSProperty_imageState', 'UIImage_setImage_NSInteger_forState');

define('IOSEnum_ControlStateNormal', 'UIControlStateNormal');

$get_property_code = function ($name, $type, $key, $value) {
    $result = '';
    switch ($key) {
        case IOSProperty_BackgroundColor:
        case IOSProperty_alpha:
            $result = "$name.$key = $value;";
            break;
        case IOSProperty_imageState:
            
            $keys = explode('_', $key);
            $values = $value;
            if (is_string($value)) {
                $values = json_decode($value, true);
            }
            
            if (is_array($values) && count($keys) == count($values)) {
                $code_arr = ["$name"];
                for ($i = 0; $i < count($keys); $i++) {
                    $key = $keys[$i];
                    $value = $values[$i];
                    $code_arr[] = "$key:$value";
                }
                $code_str = implode(' ', $code_arr);
                $result = '[' . $code_str . '];';
            }

        default;
    }

    return $result;
};

class IOSClass_NSObject {
    private $instance_name;
    static private $ivar_propertys = [];
    private $instance_value;

    public function __construct($value = NULL) {
        $this->load();
        $this->instance_value = $value;
    }
    
    protected function get_property() {
        return self::$ivar_propertys;
    }

    private function load() {
        foreach ($this->get_property() as $property) {
            $this->$property = NULL;
        }
    }



    public function get_class_type() {
        $class_name = get_class($this);
        $class_name_arr = explode('_', $class_name);
        $class_name = array_pop($class_name_arr);
        return $class_name;
    }

    public function get_property_code($key, $value) {
        global $get_property_code;
        return $get_property_code($this->instance_name, $this->get_class_type(), $key, $value);
    }
    
    public function get_all_property_code() {
        $propertys = json_decode(json_encode($this), true);

        $class_name = $this->get_class_type();

        $result = "$class_name *$this->instance_name = [$class_name new];" . PHP_EOL;

        foreach ($propertys as $key => $value) {
            if ($value !== NULL && $key != 'name') {
                $code = $this->get_property_code($key, $value);
                if ($code != '') {
                    $result .= $code . PHP_EOL;
                }
            }
        }
        return $result;
    }
}


class IOSClass_UIView extends IOSClass_NSObject{
    static private $ivar_propertys = [
        IOSProperty_BackgroundColor,
        IOSProperty_alpha
    ];

    protected function get_property() {
        return array_merge(self::$ivar_propertys, parent::get_property());
    }
}


class IOSClass_UIButton extends IOSClass_UIView {
    static private $ivar_propertys = [
        IOSProperty_imageState
    ];

    protected function get_property() {
        return array_merge(self::$ivar_propertys, parent::get_property());
    }
}



//
//$obj = new IOSClass_UIButton();
//$obj->name = 'btn';
//$obj->alpha = '1';
//$obj->setImage_forState = ['f_image', IOSEnum_ControlStateNormal];
//$obj->backgroundColor = "COLOR_G2";
//echo $obj->get_all_property_code();

