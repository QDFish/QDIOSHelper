<?php
/**
 * Created by PhpStorm.
 * User: zgzheng
 * Date: 2018/12/26
 * Time: 9:25 PM
 */

//base
define('IOSClass', 'IOSClass_');

//html <>
define('HTMLType_Input', 'input');
define('HTMLType_Select', 'select');
define('HTMLType_Datalist', 'datalist');


//UIView
define('IOSProperty_BackgroundColor', 'UIColor_backgroundColor');
define('IOSProperty_bounds', 'CGRect_bounds');
define('IOSProperty_hidden', 'CGFloat_hidden');
define('IOSProperty_cornerRadius', 'CGFloat_cornerRadius');
define('IOSProperty_borderWidth', 'CGFloat_borderWidth');
define('IOSProperty_borderColor', 'CGColor_borderColor');
define('IOSProperty_contentMode', 'UIViewContentMode_contentMode');
define('IOSProperty_alpha', 'CGFloat_alpha');

//UILabel
define('IOSProperty_text', 'NSString_text');
define('IOSProperty_font', 'UIFont_font');
define('IOSProperty_textColor', 'UIColor_textColor');
define('IOSProperty_textAlignment', 'NSTextAlignment_textAlignment');
define('IOSProperty_numberOfLines', 'NSInteger_numberOfLines');

//UIImageView
define('IOSProperty_image', 'UIImage_image');

//UIButton
define('IOSProperty_setBtnStyle', 'BtnType_setBtnStyle');
define('IOSProperty_imageState', 'UIImage_setImage_UIControlState_forState');
define('IOSProperty_titleState', 'NSString_setTitle_UIControlState_forState');
define('IOSProperty_titleColorState', 'UIColor_setTitleColor_UIControlState_forState');
define('IOSProperty_backgroundImageState', 'UIImage_setBackgroundImage_UIControlState_forState');

//UITextField
define('IOSProperty_placeholder', 'NSString_placeholder');


$surport_ui_class = [
    "UIView",
    "UIImageView",
    "UIButton",
    "UILabel",
    "UITextField"
];

$surport_data_class = [
    "CGFloat",
    "CGRect",
    "CGColor",
    "UIImage",
    "NSInteger",
    "UIControlState",
    "UIColor",
    "NSString",
    "NSTextAlignment",
    "UIViewContentMode",
    "UIFont",
    "CusIvar",
    "CusClass",
    "CusAttr",
    "CusAttrView",
    "CusOffset",
    "BtnType"
];

class IOSClass_NSObject {
    public  $CusIvar_instanceName = "";
    static private $ivar_propertys = [];
    private $instance_value;

    public function __construct($value = "") {
        $this->load();
        $this->instance_value = $value;
    }

    public function value() {
        return $this->instance_value;
    }

    static public function html_type() {
        return HTMLType_Input;
    }

    static public function html_option() {
        return [];
    }
    
    protected function get_property() {
        return self::$ivar_propertys;
    }

    private function load() {
        foreach ($this->get_property() as $property) {
            
            $num = intval(count(explode("_", $property)) / 2 - 1);
            $tmp = [];
            for ($i = 0; $i < $num; $i ++) {
                array_push($tmp, "&");
            }
            
            $this->$property = implode("&", $tmp);
        }
    }

    public function get_class_type() {
        $class_name = get_class($this);
        $class_name_arr = explode('_', $class_name);
        $class_name = array_pop($class_name_arr);
        return $class_name;
    }

    public function get_property_code($key, $value) {
        $key_arr = explode("_", $key);
        $value_arr = explode("&", $value);
        if (count($key_arr) % 2 != 0) {
            return "";
        }
        if (in_array("", $value_arr)) {
            return "";
        }

        $result = "";
        if (count($key_arr) <= 2 && strpos($key, "BtnType") === false) {
            $class_name =  IOSClass . $key_arr[0];
            $property_name = $key_arr[1];
            $value_val = $value_arr[0];
            $value_obj = new $class_name($value_val);

            if ($key == IOSProperty_cornerRadius || $key == IOSProperty_borderColor || $key == IOSProperty_borderWidth) {
                $result = "$this->CusIvar_instanceName.layer.$property_name = " . $value_obj->value() . ";";
            } else if ($key == IOSProperty_font && get_class($this) == "IOSClass_UIButton") {
                $result = "$this->CusIvar_instanceName.titleLabel.$property_name = " . $value_obj->value() . ";";
            } else {
                $result = "$this->CusIvar_instanceName.$property_name = " . $value_obj->value() . ";";
            }

        } else {
            for ($i = 0; $i < count($key_arr); $i += 2) {
                $class_name =  IOSClass . $key_arr[$i];
                $property_name = $key_arr[$i + 1];
                $value_val = $value_arr[intval($i / 2)];
                $value_obj = new $class_name($value_val);
                $value_str = $value_obj->value();
                $result .= "$property_name:$value_str ";
            }
            $result = substr($result, 0, strlen($result) - 1);
            $result = "[$this->CusIvar_instanceName $result];";
        }
        
        return $result;
    }
    
    public function get_all_property_code() {
        $propertys = json_decode(json_encode($this), true);

        $class_name = $this->get_class_type();

        $result = "$class_name *$this->CusIvar_instanceName = [$class_name new];" . PHP_EOL;

        foreach ($propertys as $key => $value) {
            if ($value !== NULL && $key != 'CusIvar_instanceName' && $key != "subviews" && $key != "constraints") {
                $code = $this->get_property_code($key, $value);
                if ($value != '' && $code != '') {
                    $result .= $code . PHP_EOL;
                }
            }
        }
        
        return $result;
    }
}


// UI Class
//define('IOSProperty_hidden', 'CGFloat_hidden');
//define('IOSProperty_cornerRadius', 'CGFloat_cornerRadius');
//define('IOSProperty_borderWidth', 'CGFloat_borderWidth');
//define('IOSProperty_borderColor', 'CGColor_borderColor');
//define('IOSProperty_contentMode', 'UIViewContentMode_contentMode');
//define('IOSProperty_alpha', 'CGFloat_alpha');

class IOSClass_UIView extends IOSClass_NSObject{
    static private $ivar_propertys = [
        IOSProperty_BackgroundColor,
        IOSProperty_bounds,
        IOSProperty_cornerRadius,
        IOSProperty_borderWidth,
        IOSProperty_borderColor,
        IOSProperty_hidden,
        IOSProperty_contentMode,
        IOSProperty_alpha
    ];

    private $subviews_name = [];
    private $constraints = [];
    private $superview_name;

    public function add_subview($subview_name) {
        $this->subviews_name[] = $this->subviews_name;
    }

    public function subviews_name() {
        return $this->subviews_name;
    }

    public function add_constraint($constraint) {
        $this->constraints[] = $constraint;
    }

    public function constraints() {
        return $this->constraints;
    }

    public function set_superview($superview_name) {
        $this->superview_name = $superview_name;
    }

    public function superview_name() {
        return $this->superview_name;
    }

    public function get_constraint_code() {

        $result = "[$this->CusIvar_instanceName mas_makeConstraints:^(MASConstraintMaker *make) {" . PHP_EOL;
        $constraints = $this->constraints();
        foreach ($constraints as $constraint) {
            $constraint_code = $constraint->constraint_code();
            $result .= "\t$constraint_code" . PHP_EOL;
        }
        $result .= "}];" . PHP_EOL . PHP_EOL;

        return $result;
    }

    protected function get_property() {
        return array_merge(self::$ivar_propertys, parent::get_property());
    }
}

class IOSClass_UIImageView extends IOSClass_UIView {
    static private $ivar_propertys = [
        IOSProperty_image
    ];
    protected function get_property() {
        return array_merge(self::$ivar_propertys, parent::get_property());
    }
}

//define('IOSProperty_text', 'NSString_text');
//define('IOSProperty_font', 'UIFont_font');
//define('IOSProperty_textColor', 'UIColor_textColor');
//define('IOSProperty_textAlignment', 'NSTextAlignment_textAlignment');
//define('IOSProperty_numberOfLines', 'NSInteger_numberOfLines');
class IOSClass_UILabel extends  IOSClass_UIView {
    static private $ivar_propertys = [
        IOSProperty_font,
        IOSProperty_textColor,
        IOSProperty_textAlignment,
        IOSProperty_numberOfLines,
        IOSProperty_text
    ];
    protected function get_property() {
        return array_merge(self::$ivar_propertys, parent::get_property());
    }
}
//
//define('IOSProperty_imageState', 'UIImage_setImage_UIControlState_forState');
//define('IOSProperty_titleState', 'UIImage_setTitle_UIControlState_forState');
//define('IOSProperty_titleColorState', 'UIImage_setTitleColor_UIControlState_forState');
//define('IOSProperty_backgroundImageState', 'UIImage_setBackgroundImage_UIControlState_forState');
class IOSClass_UIButton extends IOSClass_UIView {
    static private $ivar_propertys = [
        IOSProperty_setBtnStyle,
        IOSProperty_titleState,
        IOSProperty_titleColorState,
        IOSProperty_imageState,
        IOSProperty_backgroundImageState
    ];

    protected function get_property() {
        return array_merge(self::$ivar_propertys, parent::get_property());
    }
}

class IOSClass_UITextField extends IOSClass_UIView {
    static private $ivar_propertys = [
        IOSProperty_placeholder
    ];
    protected function get_property() {
        return array_merge(self::$ivar_propertys, parent::get_property());
    }
}

// DATA Class

class IOSClass_CGFloat extends IOSClass_NSObject {
    public function value() {
       return parent::value();
    }
}

class IOSClass_CGColor extends IOSClass_NSObject {
    public function value() {
        $value = parent::value();
        return "$value.CGColor";
    }

    static public function html_type() {
        return HTMLType_Datalist;
    }

    static public function html_option() {
        return [
            "[UIColor whiteColor]",
            "[UIColor blackColor]",
            "HB_RGBCOLOR()",
            "HB_RGBACOLOR()",
            "COLOR_G",
            "COLOR_C"
        ];
    }
}

class IOSClass_CGRect extends IOSClass_NSObject {
    public function value() {
        return parent::value();
    }

    static public function html_type() {
        return HTMLType_Datalist;
    }

    static public function html_option() {
        return [
          "CGRectMake(0, 0, , )"
        ];
    }
}



class IOSClass_NSInteger extends IOSClass_NSObject {
    public function value() {
        return parent::value();
    }
}


class IOSClass_UIColor extends IOSClass_NSObject {
    public function value() {
        return parent::value();
    }
    
    static public function html_type() {
        return HTMLType_Datalist;
    } 
    
    static public function html_option() {
        return [
            "[UIColor whiteColor]",
            "[UIColor blackColor]",
            "HB_RGBCOLOR()",
            "HB_RGBACOLOR()",
            "COLOR_G",
            "COLOR_C"
        ];
    }
}

class IOSClass_UIImage extends IOSClass_NSObject {
    public function value() {
        $value = parent::value();
        return "[UIImage imageNamed:@\"$value\"]";
    }
}

class IOSClass_NSString extends IOSClass_NSObject {
    public function value() {
        $value = parent::value();
        return "@\"$value\"";
    }
}

class IOSClass_UIFont extends IOSClass_NSObject
{
    public function value()
    {
        return parent::value();
    }

    static public function html_type()
    {
        return HTMLType_Datalist;
    }

    static public function html_option()
    {
        return [
            "[UIFont systemFontOfSize:FONT_T]",
            "[UIFont boldSystemFontOfSize:FONT_T]",
            "[UIFont systemFontOfSize:]",
            "[UIFont boldSystemFontOfSize:]"
        ];
    }
}

class IOSClass_CusIvar extends IOSClass_NSObject {
    public function value() {
        return parent::value();
    }
}

class IOSClass_CusClass extends IOSClass_NSObject {
    public function value() {
        return parent::value();
    }

    static public function html_type() {
        return HTMLType_Select;
    }

    static public function html_option() {
        global $surport_ui_class;
        return $surport_ui_class;
    }
}

class IOSClass_UIControlState extends IOSClass_NSInteger{
    static public function html_type() {
        return HTMLType_Datalist;
    }

    static public function html_option() {
        return [
            "UIControlStateNormal",
            "UIControlStateHighlighted",
            "UIControlStateDisabled",
            "UIControlStateSelected"
        ];
    }
}

class IOSClass_UIViewContentMode extends IOSClass_NSInteger{
    static public function html_type() {
        return HTMLType_Datalist;
    }

    static public function html_option() {
        return [
            "UIViewContentModeScaleToFill",
            "UIViewContentModeScaleAspectFit",
            "UIViewContentModeScaleAspectFill"
        ];
    }
}

class IOSClass_NSTextAlignment extends IOSClass_NSInteger{
    static public function html_type() {
        return HTMLType_Datalist;
    }

    static public function html_option() {
        return [
            "NSTextAlignmentLeft",
            "NSTextAlignmentCenter",
            "NSTextAlignmentRight"
        ];
    }
}

class IOSClass_BtnType extends IOSClass_NSInteger{
    static public function html_type() {
        return HTMLType_Datalist;
    }

    static public function html_option() {
        return [
            "BtnType21",
            "BtnType22",
            "BtnType23"
        ];
    }
}


class IOSClass_CusAttr extends IOSClass_NSObject{
    static public function html_type() {
        return HTMLType_Datalist;
    }

    static public function html_option() {
        return [
            "L",
            "R",
            "T",
            "B",
            "W",
            "H",
            "CY",
            "CX",
            "WH",
            "HW",
            "LR",
            "RL",
            "TB",
            "BT",
        ];
    }
}

class IOSClass_CusAttrView extends IOSClass_NSObject{
    static public function html_type() {
        return HTMLType_Datalist;
    }
}

class IOSClass_CusOffset extends IOSClass_NSObject{
    static public function html_type() {
        return HTMLType_Input;
    }
}

// Constraints

class IOSClass_Constraints {
    public $super_view = "";
    public $attr = "";
    public $view = "";
    public $offset = "";

    public function __construct($value) {
        foreach ($value as $k => $v) {
            $k_arr = explode("_", $k);
            $v_arr = explode("&", $v);
            for ($i = 1; $i < count($k_arr) ; $i += 2) {
                $val_idx = intval($i / 2);
                $name = $k_arr[$i];
                $val = $v_arr[$val_idx];
                $this->$name = $val;
            }
        }
    }

    static private $attribute_maps = [
        "L" => "mas_left",
        "R" => "mas_right",
        "T" => "mas_top",
        "B" => "mas_bottom",
        "W" => "mas_width",
        "H" => "mas_height",
        "CX" => "mas_centerX",
        "CY" => "max_centerY",
    ];

    public function constraint_code() {
        $attr = strtoupper($this->attr);
        $first_attribute = "";
        $second_attribute = "";
        $view = "";
        $offset = "";

        $is_same_attribute = false;
        if ($attr == "CX" || $attr == "CY" || strlen($attr) == 1) {
            $first_attribute = self::$attribute_maps[$attr];
            $is_same_attribute = true;
        } else if (strlen($attr) == 2) {
            $first_attribute = self::$attribute_maps[substr($attr, 0, 1)];
            $second_attribute = self::$attribute_maps[substr($attr, 1, 1)];
        }
        
        if ($first_attribute != "" && $this->super_view != "") {

            $attr_array = explode("_", $first_attribute);
            $first_attribute = array_pop($attr_array);
            $offset_val = intval($this->offset);

            //right bottom 自动转为负值
            if ($first_attribute == "right" || $first_attribute == "bottom") {
                $offset_val = -$offset_val;
            }

            //相同属性缩写
            if ($is_same_attribute) {
                $view = $this->view == "" ? "@(" . $offset_val . ")" : $this->view;
                $offset = $this->view == "" ? "" : ".offset($offset_val)";
                if ($this->view == "") {
                    $offset = "";
                } else {
                    if ($offset_val != 0) {
                        $offset = ".offset($offset_val)";
                    } else {
                        $offset = "";
                    }
                }
            } else if (!$is_same_attribute) {
                //offset为0可省略
                $offset = $offset_val != 0 ? ".offset($offset_val)" : "";

                //view为空则为super_view
                $view = $this->view == "" ? $this->super_view : $this->view;
            }

            if ($second_attribute != "") {
                $second_attribute = ".$second_attribute";
            }

            return "make.$first_attribute.equalTo($view$second_attribute)$offset;";
        }
        return "";
    }
}

//
//$obj = new IOSClass_UIButton();
//$obj->name = 'btn';
//$obj->alpha = '1';
//$obj->setImage_forState = ['f_image', IOSEnum_ControlStateNormal];
//$obj->backgroundColor = "COLOR_G2";
//echo $obj->get_all_property_code();

