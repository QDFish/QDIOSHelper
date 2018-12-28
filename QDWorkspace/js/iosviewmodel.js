/**
 * Created by zgzheng on 2018/12/27.
 */
$(document).ready(function () {
   bind_subview_click();
});

function bind_subview_click() {
    $(".subviews_btn").click(function () {
        $( this ).parent().parent().append(subview());
        $(".subviews_btn").off("click");
        bind_subview_click();
    });
}

function subview() {
    var base_div = "<div class=\"base_div\">";
    var content_div = "<div class=\"content_div\">";
    var classname_btn = "<button class=\"classname_btn\">class|name</button>";
    var constraints_btn = "<button class=\"constraints_btn\">constraints</button>";
    var subviews_btn = "<button class=\"subviews_btn\">subviews</button>";
    var end_div = "</div>";
    return base_div +
        content_div +
        classname_btn +
        constraints_btn +
        subviews_btn +
        end_div +
        end_div;
}

