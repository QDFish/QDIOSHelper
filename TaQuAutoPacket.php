<?php
/**
 * Created by PhpStorm.
 * User: zgzheng
 * Date: 2018/11/8
 * Time: 上午11:56
 */
require_once 'AutoPacketTool.php';

///Users/zgzheng/TouchiOS_new
$cd_c = "cd /Users/guess/TaQu";
$fetch_c = "git fetch origin";
$branch_list_c = "git branch -r";
$cur_branch_c= "git symbolic-ref --short -q HEAD";

$branch_list_shell = gd_shell_array([$cd_c, $fetch_c, $branch_list_c]);
exec($branch_list_shell, $branch_list_result, $branch_list_status);
if($branch_list_status){
    echo "获取分支列表失败";
    exit(1);
}

$cur_branch_shell = gd_shell_array([$cd_c, $cur_branch_c]);
exec($cur_branch_shell, $cur_branch_result, $cur_branch_status);
if($cur_branch_status){
    echo "获取当前分支名失败";
    exit(1);
}

echo <<<HTMLHeader
<!DOCTYPE html>
<html>
<script language="javascript">
   function CheckPost () {    
     alert("确定后进入打包路程，请勿重复提交，等待响应结束！");            
     return true;
   }
</script>
<body>
HTMLHeader;
echo "<h1>iOS打包</h1>";
echo "<p><img src=\"/saya.gif\"/></p>";
echo "<form action='TaQuAutoDataHandle.php' method='post' onsubmit='return CheckPost();'>";
echo "当前分支: ";
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
echo "<select name=\"target\">";
echo "<option value=\"TaQuTest\">TaQuTest</option>";
echo "<option value=\"TaQuBuild\">TaQuBuild</option>";
echo "</select>";

echo "<br />";
echo "IPA名后缀: ";
echo "<input type=\"text\" name=\"ext\">";

echo "<br />";
echo "项目组: ";
echo "<select name=\"group\">";
echo "<option value=\"1\">直播</option>";
echo "<option value=\"2\">社区</option>";
echo "<option value=\"3\">商城</option>";
echo "<option value=\"4\">融合包</option>";
echo "<option value=\"5\">内测包</option>";
echo "</select>";

echo "<br />";
echo "ipa包文件夹名: ";
echo "<input type=\"text\" name=\"dir\">";


echo "<br />";
echo "<br />";
echo "<font color='red'>ps:打包需要大概10到20分钟<font><br/>";

echo <<<HTMLFoot
<br><br>
<input type="submit" value="打包">
</form>

</body>
</html>
HTMLFoot
?>
