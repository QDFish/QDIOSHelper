/**
 * Created by zgzheng on 2019/1/13.
 */

function load_loading_html() {
    $("body").prepend(
        "<div class=\"loading_bg\">" +
        "<img id='loading' src=\"/QDWorkspace/resource/loading.png\">" +
        "</div>"
    )
}

function load_popview_html(save = undefined, hide = undefined) {
    $("body").prepend(
        "<div class='pop_bg'>" +
            "<div class='pop_change_bg'>" +
            "</div>" +
            "<div class='pop_view'>" +
                "<button class='close_btn'>close</button>" +
                "<div class='pop_content'>" +
                "</div>" +
                "<button class='save_btn'>save</button>" +
            "</div>" +
        "</div>"
    );
    
    bind_popclose_click(hide);
    bind_save_click(save);
}

function empty_pop_content() {
    var pop_content = $(".pop_content");
    pop_content.empty();
    return pop_content;
}

function bind_popclose_click(finish) {
    $(".close_btn").click(function () {
        hide_pop(finish);
    });
}

function bind_save_click(finish) {
    $(".save_btn").click(function () {
        hide_pop(function () {
            if (finish) {
                finish();
            }
        });
    });
}

function show_pop() {
    $("body").css("overflow", "hidden");
    $(".pop_bg")
        .css("visibility", "visible")
        .animate({
            opacity: 1
        }, 500, function () {

        });
}


function hide_pop(finish) {
    $("body").css("overflow", "auto");
    $(".pop_bg").animate({
        opacity: 0
    }, 200, function () {
        $(this).css("visibility", "hidden");
        if (finish) {
            finish();
        }
        $(".pop_content").empty();
    });
}

function show_savebtn(show) {
    if (show) {
        $(".save_btn").css("visibility", "visible");
    } else {
        $(".save_btn").css("visibility", "hidden");
    }
}

function show_loading() {
    $("body").css("overflow", "hidden");
    $(".loading_bg")
        .css("visibility", "visible")
        .animate({
            opacity: 0.5
        }, 500, function () {

        });
    rotate_sel($("#loading"));
}

function hide_loading(finish) {
    $("body").css("overflow", "auto");
    $(".loading_bg").animate({
        opacity: 0
    }, 500, function () {
        $(this).css("visibility", "hidden");
        stop_rotate_sel($("#loading"));
        if (finish) {
            finish();
        }
    });
}

var degree = 0, timer;

function rotate_sel(selector) {
    selector.css({"-webkit-transform": "rotate(" + degree + "deg)"});
    timer = setTimeout(function () {
        degree++;
        rotate_sel(selector);
    }, 5);
}

function stop_rotate_sel(selector) {
    degree = 0;
    selector.css({"-webkit-transform": "rotate(" + degree + "deg)"});
    clearTimeout(timer);
}