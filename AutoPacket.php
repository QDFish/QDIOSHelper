<?php
/**
 * Created by PhpStorm.
 * User: zgzheng
 * Date: 2018/11/8
 * Time: 上午11:56
 */


$target_type = $_GET['target_type'];

require_once 'AutoPacketTool.php';
require_once 'AutoPacketConstant.php';

///Users/zgzheng/TouchiOS_new
$fetch_c = "git fetch origin";
$branch_list_c = "git branch -r";
$cur_branch_c= "git symbolic-ref --short -q HEAD";

$branch_list_shell = gd_shell_array([$cd_git_c, $fetch_c, $branch_list_c]);
exec($branch_list_shell, $branch_list_result, $branch_list_status);
if($branch_list_status){
    echo "获取分支列表失败";
    exit(1);
}

$cur_branch_shell = gd_shell_array([$cd_git_c, $cur_branch_c]);
exec($cur_branch_shell, $cur_branch_result, $cur_branch_status);
if($cur_branch_status){
    echo "获取当前分支名失败";
    exit(1);
}


$pid = null;
if (file_exists('/tmp/lock.file')) {
    $pid = file_get_contents('/tmp/lock.file');
}

require_once 'iOSPlistInit.php';


echo <<<HTMLHeader
<!DOCTYPE html>
<html>
<script language="javascript">

function CheckPost () {
    alert("确定后进入打包路程，请勿重复提交，等待响应结束！");
    return true;
}
   
function change() {     
    var targetSelect = document.getElementById("target");    
    var index = targetSelect.selectedIndex;    
    var targetName = targetSelect.options[index].value;
    if (targetName == "$test_target_key") {
        document.getElementById("version").value="$version_dic[$test_target_key]";
        document.getElementById("build").value="$build_dic[$test_target_key]";
    } else if (targetName == "$build_target_key") {
        document.getElementById("version").value="$version_dic[$build_target_key]";
        document.getElementById("build").value="$build_dic[$build_target_key]";
    }
}


function killAction(pid) {
    var r = confirm("are you sure to kill pid?");
    if (r == true) {
        var text = HttpGet("KillPid.php?pid=" + pid);
        if (text) {
            alert("" + text);
        } else {
            document.getElementById("pid_tip").hidden = true;
            document.getElementById("kill").hidden = true;
        }
    } 
}

function HttpGet(theUrl) {
    var xmlHttp = null;
    xmlHttp = new XMLHttpRequest();
    xmlHttp.open("get", theUrl, false);
    xmlHttp.send(null);
    return xmlHttp.responseText;
}
  
</script>
<body>
HTMLHeader;
echo "<h1>iOS打包</h1>";
echo "<font color='red'><h3>灰度的选项无效,需要工程布置,具体询问各项目开发人员</h3><font> <font><font>";
echo "<p><img src=\"/saya.gif\"/>";

if ($pid != null) {
    echo "<font color='red' id='pid_tip'>有人正在打包 pid=$pid<font>";
    echo "等待或者<button id='kill' onclick='killAction($pid)'>kill him!</button>";
}

echo "</p>";
//echo '<pre>';
//var_dump( $build_dic );
//var_dump( $version_dic );
//echo '</pre>';
echo "<form action='AutoDataHandle.php' method='post' onsubmit='return CheckPost();'>";
echo "<font color='black'>当前分支: <font>";
echo "<select name=\"select_branch\">";
foreach ($branch_list_result as $branch) {
    $value = end(explode(DIRECTORY_SEPARATOR, $branch));

    if ($value == end($cur_branch_result)) {
        echo "<option value=\"$value\" selected='selected'>$branch</option>";
    } else {
        echo "<option value=\"$value\">$branch</option>";
    }
}
echo "</select>";

echo "<br />";
echo "目标名: ";
echo "<select name=\"target\" id='target' onchange='change(this.id)'>";
echo "<option value=\"TaQuTest\" selected='selected'>TaQuTest</option>";
echo "<option value=\"TaQuBuild\">TaQuBuild</option>";
echo "</select>";

echo "<br />";
echo "Version: ";
echo "<input type=\"text\" id='version' name=\"version\" value='$version_dic[$test_target_key]'>";

echo "<br />";
echo "Build: ";
echo "<input type=\"text\" id='build' name=\"build\" value='$build_dic[$test_target_key]'>";

echo "<br />";
echo "IPA名后缀: ";
echo "<input type=\"text\" name=\"ext\">";

echo "<br />";
echo "是否灰度: ";
echo "<select name=\"is_gray\" id='is_gray'>";
if ($is_gray == true) {
    echo "<option value=\"1\" selected='selected'>是</option>";
    echo "<option value=\"0\">不是</option>";
} else {
    echo "<option value=\"1\">是</option>";
    echo "<option value=\"0\" selected='selected'>不是</option>";
}

echo "</select>";

echo "<br />";
echo "项目组: ";
echo "<select name=\"group\">";
foreach ($group_value as $key => $value) {
    echo "<option value=\"$value\">$key</option>"; 
}
//echo "<option value=\"1\">直播</option>";
//echo "<option value=\"2\">社区</option>";
//echo "<option value=\"3\">商城</option>";
//echo "<option value=\"4\">融合包</option>";
//echo "<option value=\"5\">内测包</option>";
echo "</select>";

echo "<br />";
echo "ipa包文件夹名: ";
echo "<input type=\"text\" name=\"dir\">";


echo "<br />";
echo "<br />";
echo "<font color='red'>ps:打包需要大概10到20分钟<font><br/>";

echo "<input type=\"hidden\" name=\"target_type\" value='$target_type'>";

echo <<<HTMLFoot
<br><br>
<input type="submit" value="打包">
</form>

</body>
</html>
HTMLFoot
?>
