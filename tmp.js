/**
 * Created by zgzheng on 2018/11/20.
 */
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