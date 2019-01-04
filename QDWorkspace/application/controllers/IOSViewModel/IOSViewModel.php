<?php

require_once "IOSUIKit.php";
/**
 * Created by PhpStorm.
 * User: zgzheng
 * Date: 2018/12/26
 * Time: 8:16 PM
 */
class IOSViewModel extends CI_Controller
{
    public static $base_path = 'IOSViewModel/';
    public static $class_prefix = "IOSClass";

    public function index() {
        $this->load->helper('url');
        $this->load->view('NormalHtmlBegin');
        $this->load->view(self::$base_path . 'IOSViewHeader');
        $this->load->view(self::$base_path . 'IOSViewHtmlBody');
        $this->load->view('NormalHtmlEnd');
    }

    public function extraList() {
        $result = [];
        global $surport_data_class, $surport_ui_class;
        foreach ($surport_data_class as $data_class) {
            $class_name = self::$class_prefix . "_" . $data_class;
            $type = call_user_func([$class_name, "html_type"]);
            $options = call_user_func([$class_name, "html_option"]);
            $result[$data_class] = [
                "type" => $type,
                "options" => $options,
                "class" => []
            ];
        }

        foreach ($surport_ui_class as $ui_class) {
            $class_name = self::$class_prefix . "_" . $ui_class;
            $instance = new $class_name();
            $instance_str = json_encode($instance);
            $instance_arr = json_decode($instance_str, true);
            $result[$ui_class] = [
                "type" => "",
                "options" => [],
                "class" => $instance_arr
            ];
        }

        echo json_encode($result);;
    }
    
    public function classProperties($class) {
        $class_name = self::$class_prefix . "_" . $class;
        $instance = new $class_name();
        echo json_encode($instance);
    }

    public function analysis() {
        $result = [];
        $view_result = "";
        $constraint_result = "";
        $json = json_decode(file_get_contents("php://input"), true);
        $json = $json["data"];
        $views = $this->recursionAnalysis($json, "");
        foreach ($views as $view) {
            if ($view->superview_name() == '') {
                continue;
            }
            $view_result .= $view->get_all_property_code();
            $superview_name = $view->superview_name();
            $cur_name = $view->CusIvar_instanceName;
            $view_result .= "[$superview_name addSubview:$cur_name];" . PHP_EOL . PHP_EOL;
            
            $constraint_result .= $view->get_constraint_code();
        }
        
        $result["views"] = $view_result;
        $result["constraints"] = $constraint_result;
        
        echo json_encode($result);
    }

    private function recursionAnalysis($data, $superview_name) {
        $views = [];
        $class_name_key = "CusClass_classname";
        $subviews_key = "subviews";
        $constraints_key = "constraints";
        if (!isset($data[$class_name_key])) {
            return $views;
        }
        $class_name = $data[$class_name_key];
        $class_name = self::$class_prefix . "_" . $class_name;
        $instance = new $class_name();
        $instance->set_superview($superview_name);
        foreach ($data as $key => $value) {
            if ($key != $class_name_key && $key != $subviews_key && $key != $constraints_key) {
                $instance->$key = $value;
            }
        }
        
        
        if (isset($data[$constraints_key])) {
            $constraints_data = $data[$constraints_key];
            foreach ($constraints_data as $key => $value) {
                $constraint = new IOSClass_Constraints($value);
                $constraint->super_view = $superview_name;
                $instance->add_constraint($constraint);
            }
        }
        array_push($views, $instance);

        if (isset($data[$subviews_key])) {
            $subviews_data = $data[$subviews_key];
            $subviews = [];
            foreach ($subviews_data as $subview_data) {
                $subviews = array_merge($subviews, $this->recursionAnalysis($subview_data, $instance->CusIvar_instanceName));
            }
            $views = array_merge($views, $subviews);
        }

        return $views;
    }

    public function help() {
        $help = <<<HELP
    Tips
    
0、第一个视图只需去设置相应的变量名(instanceName)即可,比如在vc中是self.view,在view中是self等等,相当于根视图         
         
1、class|name按钮用来设置视图本身的属性,如果设置不全或者未设置,后面会跟上红色标注的unset

2、constraints按钮用来设置视图约束,如果设置不全或者未设置,后面会跟上红色标注的unset

3、subviews按钮用来添加子视图

4、analysis用来生成相应的代码

4、当设置视图本身的属性时,放空的属性不会被分析,所有只需设置需要的属性,很多属性具有预设值。没有预设值比如UIImage,只需输入相应的图片名即可

5、约束有三个属性可以设置,分别是attr, view, offset,按照的是Masonry的规则来
attr表示约束本身,比如left,right用LR表示,他们都有预设值,根据预设值去设置,attr为必须设置的属性,否则不会被保存
view表示相对于的约束对象,具有变化的预设值,分别为父类,自己,还有同胞。view可以省略,默认为父视图
offset同约束值offset,如果attr的值为L,R,T,B中的其中之一,可以大胆的使用正值,做了方便处理,offset可以省略,为0

6、当约束为高度或者宽度时,通过省略view的值而设置offset的值去设置相应的高度以及宽度。
HELP;
        echo $help;
    }
}