/**
 * Created by zgzheng on 2018/12/27.
 */

var cur_pop_selector;
var img_list = [];

$(document).ready(function () {
    bind_total();
    bind_save_click();
    bind_analysis_click();
    bind_close_click();
    bind_help_click();
    bind_png_drop();
    bind_upload();
    get_extra_list();
});

function off_total() {
    $(".subviews_btn").off("click");
    $(".remove_btn").off("click");
    $(".classname_btn").off("click");
    $(".constraints_btn").off("click");
}

function bind_total() {
    bind_subview_click();
    bind_remove_click();
    bind_classname_click();
    bind_constraints_click();
}

function ShowTheObject(obj) {
    var des = "";
    for (var name in obj) {
        des += name + ":" + obj[name] + ";\n";
    }
    return des;
}

//bind about

function bind_upload() {
    $(".upload_btn").click(function () {
       deal_imgs();
    });
}

function bind_png_drop() {
    $(".png_div").on({
        drop : function (event) {
            event.preventDefault();
            event.stopPropagation();

            var dt = event.dataTransfer || (event.originalEvent && event.originalEvent.dataTransfer);
            var items = event.target.items || (dt && dt.items);
            if (items.length == 0) {
                return false;
            }

            $(this).empty();


            img_list.splice(0, img_list.length);
            for (var i = 0; i < items.length; i++) {
                var item = items[i];
                traverse_file_tree(item.webkitGetAsEntry());
            }

        },
        dragover : function (event) {
            event.preventDefault();
            event.stopPropagation();
        }
    })
}

function bind_help_click() {
    $(".help").click(function () {
        show_loading();
        var id = $(this).attr("id");
        
       $.ajax({
           type:"GET",
           url: id,
           dataType:"text",
           success:function (data) {
               hide_loading();
               var pop_content = $(".pop_content");
               pop_content.empty();
               $(".save_btn").css("visibility", "hidden");
               var html_result = "<div class=\"help_div\"><span>" + data + "</span></div>";
               pop_content.html(html_result);
               show_pop();
           }
       });
    });
}

function bind_close_click() {
    $(".close_btn").click(function () {
        hide_pop();
    });
}

function bind_analysis_click() {
    $(".analysis_btn").click(function (event) {
        var result = recursion_selector($("body").children(".base_div"));
        show_loading();
        $.ajax({
            type:"POST",
            url:"analysis",
            dataType:"json",
            data:JSON.stringify({"data" : result}),
            success:function (data) {
                hide_loading();
                reset_analysis_content(data);
                show_pop();
            }
        });
    });
}

function bind_constraints_click() {
    $(".constraints_btn").click(function () {
        cur_pop_selector = $(this);
        var data = $(this).data();
        get_extra_list(function () {
            reset_constraints_content(data);
            show_pop();
        });
    });
}

function bind_add_constraints_click() {
    $(".add_constraints_btn").click(function () {
        append_constraints_content();
    });
}



function bind_subview_click() {
    $(".subviews_btn").click(function () {
        $( this ).parent().parent().append(subview_html());
        off_total();
        bind_total();
    });
}

function bind_classname_click() {
    $(".classname_btn").click(function () {
        cur_pop_selector = $(this);
        var propertys = $(this).data();
        if (!("CusClass_classname" in propertys)) {
            propertys = {"CusClass_classname" : ""};
            $(this).data(propertys);
        }

        get_extra_list(function () {
            reset_property_content(propertys);

            if (cur_pop_selector.data("CusClass_classname") == "") {
                class_change($("select[name=\"classname\"]").val());
            } else {
                reset_property_content(cur_pop_selector.data());
            }

            show_pop();
        });
    });
}

function bind_classname_change() {
    $("select[name=\"classname\"]").change(function () {
       class_change($(this).val());
    });
}


function bind_remove_click() {
    $(".remove_btn").click(function () {
        $( this ).parent().parent().remove();
    });
}

function bind_save_click() {
    $(".save_btn").click(function () {
        hide_pop(function () {
            if (cur_pop_selector.attr("class") == "classname_btn") {
                cur_pop_selector.removeData();
                save_classname_content();
            } else if (cur_pop_selector.attr("class") == "constraints_btn") {
                cur_pop_selector.removeData();
                save_constraints_content();
            }
        });
    });
}

function bind_rm_constraint_click() {
    $(".remove_constraint_btn").click(function () {
       $(this).parent().parent().remove();
    });
}


function class_change(class_name) {
    cur_pop_selector.removeData();
    cur_pop_selector.data({"CusClass_classname" : class_name});

    extra_list = $("body").data();
    var data = extra_list[class_name]["class"];
    cur_pop_selector.data(data);
    reset_property_content(cur_pop_selector.data());
}

function recursion_selector(selector) {
    var result;
    var content_sel = selector.children(".content_div");
    var classname_sel = content_sel.children(".classname_btn");
    var constraint_sel = content_sel.children(".constraints_btn");

    result = classname_sel.data();
    result["subviews"] = [];
    result["constraints"] = constraint_sel.data();

    selector.children(".base_div")
        .each(function () {
            result["subviews"].push(recursion_selector($(this)));
        });
    return result;
}

function reset_analysis_content(data) {
    var pop_content = $(".pop_content");
    pop_content.empty();
    $(".save_btn").css("visibility", "hidden");
    var view_code = data["views"];
    var constraint_code = data["constraints"];

    var html_result = "";
    if (view_code != "" ) {
        html_result +=
            "<div class=\"property_div\">" +
            "<span style='white-space: pre-wrap; font-size: small'>" + view_code + "</span>" +
            "</div>";
    }

    if (constraint_code != "") {
        html_result +=
            "<div class=\"constraint_div\">" +
            "<span style='white-space: pre-wrap; font-size: small'>" + constraint_code + "</span>" +
            "</div>";
    }

    pop_content.append(html_result);
}

function reset_property_content(data) {
    extra_list = $("body").data();
    pop_content = $(".pop_content");
    pop_content.empty();
    var html_result = "";
    for (key in data) {
        if (key == "subviews" || key == "constraints") {
            continue;
        }

        var value_str = data[key];
        var keys = key.split("_");
        var values = value_str.split("&");
        var span_result = "";
        var key_length = Object.keys(keys).length;
        if (key_length % 2 == 0) {
            for (var i = 0; i < key_length; i += 2) {
                var class_type = keys[i];
                var name = keys[i + 1];
                var value = values[i / 2];
                if (!value) {
                    value = "";
                }
                var html_type = extra_list[class_type]["type"];
                var options = extra_list[class_type]["options"];

                if (html_type == "input") {
                    span_result +=
                        "<span class=\"" + class_type + "\">" +
                        name + ": <input name=\"" + name + "\" type=\"text\" value=\"" + value + "\">" +
                        "</span>";
                } else if (html_type == "select") {

                    var option_result = "";
                    for (var idx_option in options) {
                        var option = options[idx_option];
                        if (option == value) {
                            option_result +=
                                "<option value=\"" + option + "\" selected=\"selected\">" + option + "</option>"
                        } else  {
                            option_result +=
                                "<option value=\"" + option + "\">" + option + "</option>"
                        }
                    }

                    span_result +=
                        "<span class=\"" + class_type + "\">" +
                        name + ": <select name=\"" + name + "\">" +
                        option_result +
                        "</select>" +
                        "</span>";

                } else if (html_type == "datalist") {
                    var list_result = "<datalist id=\"" + class_type + "\">";
                    for (var idx_list in options) {
                        var list = options[idx_list];
                        list_result +=
                            "<option value=\"" + list + "\">";
                    }
                    list_result += "</datalist>";

                    span_result +=
                        "<span class=\"" + class_type + "\">" +
                        name + ": <input name=\"" + name + "\" list=\"" + class_type +"\" value=\"" + value + "\">" +
                        list_result +
                        "</span>";
                }
            }

            html_result +=
                "<div class=\"property_div\">" +
                span_result +
                "</div>";

        }
    }

    pop_content.append(html_result);
    bind_classname_change();
}

function constraint_content(constraint_data) {
    html_result = "";
    extra_list = $("body").data();
    for (var constraint_key in constraint_data) {
        var constraint_value = constraint_data[constraint_key];
        var constraint_keys = constraint_key.split("_");
        var constraint_values = constraint_value.split("&");
        var constraint_key_length = Object.keys(constraint_keys).length;
        var span_result = "";

        for (var i = 0; i < constraint_key_length; i += 2) {
            var class_type = constraint_keys[i];
            var name = constraint_keys[i + 1];
            var value = constraint_values[i / 2];

            var html_type = extra_list[class_type]["type"];
            var options = extra_list[class_type]["options"];

            if (class_type == "CusAttrView") {
                options = [];
                var base_divs = [];
                var base_div_sel = cur_pop_selector.parent().parent();
                if (base_div_sel.parent().attr("class") == "base_div") {
                    base_divs.push(base_div_sel.parent());
                }
                base_divs.push(base_div_sel);
                base_div_sel.siblings()
                    .each(function () {
                        if ($(this).attr("class") == "base_div") {
                            base_divs.push($(this));
                        }
                    });

                for (var cur_idx in base_divs) {
                    var cur_base_div = base_divs[cur_idx];

                    var properties = cur_base_div.children(".content_div").children(".classname_btn").data();
                    if (Object.keys(properties).length > 0) {
                        var cur_base_name = properties["CusIvar_instanceName"] ? properties["CusIvar_instanceName"] : "name";
                        options.push(cur_base_name);
                    }
                }
            }

            if (html_type == "input") {
                span_result +=
                    "<span class=\"" + class_type + "\">" +
                    name + ": <input name=\"" + name + "\" type=\"text\" value=\"" + value + "\">" +
                    "</span>";
            }  else if (html_type == "datalist") {
                var list_result = "<datalist id=\"" + class_type + "\">";
                for (var idx_list in options) {
                    var list = options[idx_list];
                    list_result +=
                        "<option value=\"" + list + "\">";
                }
                list_result += "</datalist>";

                span_result +=
                    "<span class=\"" + class_type + "\">" +
                    name + ": <input name=\"" + name + "\" list=\"" + class_type +"\" value=\"" + value + "\">" +
                    list_result +
                    "</span>";
            }
        }
        html_result =
            "<div class=\"constraint_div\">" +
            span_result +
            "<span><button class='remove_constraint_btn'>RM</button></span>" +
            "</div>";
    }
    return html_result;
}

function reset_constraints_content(data) {
    var pop_content_sel = $(".pop_content");
    var rm_constraint_sel = $(".remove_constraint_btn");
    rm_constraint_sel.off("click");
    $(".add_constraints_btn").off("click");
    pop_content_sel.empty();

    var result_html = "";

    result_html += "<button class=\"add_constraints_btn\">ADD</button>";
    for(var key in data) {
        var constraint_data = data[key];
        result_html += constraint_content(constraint_data);
    }
    pop_content_sel.html(result_html);
    bind_rm_constraint_click();
    bind_add_constraints_click();
}

function append_constraints_content(data) {
    var pop_content_sel = $(".pop_content");
    var constraint_data = {"CusAttr_attr_CusAttrView_view_CusOffset_offset" : "&&"};
    var rm_constraint_sel = $(".remove_constraint_btn");
    rm_constraint_sel.off("click");
    pop_content_sel.append((constraint_content(constraint_data)));
    bind_rm_constraint_click();
}


function save_classname_content() {
    $("select[name=\"classname\"]").off("change");
    var pop_content_sel =  $(".pop_content");
    var class_name = "class";
    var instance_name = "name";
    pop_content_sel.find(".property_div")
        .each(function () {
            var keys = [];
            var values = [];
            $(this).find("span")
                .each(function () {
                    var class_type = $(this).attr("class");
                    var name;
                    var value;
                    if ($(this).find("input").length != 0) {
                        name = $(this).find("input").attr("name");
                        value = $(this).find("input").val();
                    } else if ($(this).find("select").length != 0) {
                        name = $(this).find("select").attr("name");
                        value = $(this).find("select").val();
                    }
                    keys.push(class_type);
                    keys.push(name);
                    values.push(value);

                    //classname instancename
                    if (name == "classname" && value && value != "") {
                        class_name = value;
                    }

                    if (name == "instanceName" && value && value != "") {
                        instance_name = value;
                    }
                });
            var key_str = keys.join("_");
            var value_str = values.join("&");
            cur_pop_selector.data(key_str, value_str);
        });
    if (class_name == "class" || instance_name == "name") {
        cur_pop_selector.html(class_name + "|" + instance_name + "<span class='tip'>(unset)</span>");
    } else {
        cur_pop_selector.html(class_name + "|" + instance_name);
    }
}

function save_constraints_content() {
    $(".add_constraints_btn").off("click");
    var pop_content_sel =  $(".pop_content");
    pop_content_sel.find(".constraint_div")
        .each(function () {
            var keys = [];
            var values = [];
            var attr_val= "-1";
            $(this).find("span")
                .each(function () {
                    var input_sel = $(this).find("input");
                    if (input_sel.length > 0) {
                        var class_type = $(this).attr("class");
                        var name = input_sel.attr("name");
                        var value = input_sel.val();
                        keys.push(class_type);
                        keys.push(name);
                        values.push(value);
                        if (class_type == "CusAttr") {
                            attr_val = value;
                        }
                    }
                });

            attr_val = attr_val.toUpperCase();
            var options = $("body").data()["CusAttr"]["options"];
            if (options.indexOf(attr_val) != -1) {
                var key_str = keys.join("_");
                var value_str = values.join("&");
                var constraint_json = {};
                constraint_json[key_str] = value_str;
                cur_pop_selector.data(attr_val, constraint_json);
            }
        });
    var tip_sel = cur_pop_selector.find(".tip");
    if (Object.keys(cur_pop_selector.data()).length > 0) {
        tip_sel.css("display", "none");
    } else {
        tip_sel.css("display", "relative");
    }
}

function get_extra_list(finish) {
    var extra_list = $("body").data();

    if (!extra_list || Object.keys(extra_list).length  == 0) {
        show_loading();
        $.ajax({
            type:"GET",
            url:"extraList",
            dataType:"json",
            success:function (data) {
                hide_loading();
                extra_list = data;
                $("body").data(extra_list);
                if (finish) {
                    finish(extra_list);
                }

            }
        });
    } else {
        if (finish) {
            finish(extra_list);
        }
    }
}


function deal_imgs() {
    upload_imgs(function (result) {
        if (result == true) {

            var result_json = {};
            result_json["imgs"] = {};

            $(".png_container_content")
                .each(function () {
                    var png_input = $(this).find(".png_input");
                    if (png_input.val().length != 0) {
                        var img = $(this).find(".png");
                        if (img.attr("id").length != 0) {
                            result_json["imgs"][img.attr("id")] = png_input.val();
                        }
                    }
                });

            if (Object.keys(result_json["imgs"]).length == 0) {
                alert("至少修改一张图片");
            } else {
                $.ajax({
                    url : "deal_imgs",
                    type : "POST",
                    dataType : "text",
                    data : JSON.stringify(result_json),
                    success : function (data) {
                        $("body").append("<a id='tmp_download' href='"+ data +"' download='" + data + "'>download</a>");
                        document.getElementById("tmp_download").click();
                        $("#tmp_download").remove();
                    },
                    error : function () {
                        alert("deal failed success");
                    }
                });
            }
        }
    });
}

function upload_imgs(finish) {


    var data = new FormData();

    for (var i = 0; i < img_list.length; i++){
        var file = img_list[i];
        data.append(file.name, file);
    }

    show_loading();
    $.ajax({
        url : "upload_imgs",
        type : "POST",
        data : data,
        contentType : false,
        processData : false,
        success : function (data) {
            hide_loading();
            if (finish) {
                finish(true);
            }
        },
        error : function (error) {
            hide_loading();
            if (finish) {
                alert("deal img failed");
                finish(false);
            }
        }
    });
}

function traverse_file_tree(item) {
    if (item.isFile) {
        item.file(function(file) {
            if (file.type.indexOf('image') !== -1 && file.type.indexOf('gif') == -1) {

                if (file.name.indexOf("@2x") !== -1) {
                    create_img_html(file);
                }

                if (file.name.indexOf("@2x") != -1 || file.name.indexOf("@3x") != -1) {
                    img_list.push(file);
                }
            }
        });
    } else if (item.isDirectory) {
        var dir_reader = item.createReader();
        dir_reader.readEntries(function(entries) {
            for (var i=0; i<entries.length; i++) {
                traverse_file_tree(entries[i]);
            }
        });
    }
}

function create_img_html(file) {

    var reader = new FileReader();
    reader.onload = function (cur_file) {
        var name = file.name;
        var index = file.name.indexOf("@");
        if (index != -1) {
            name = name.substring(0, index);
        }

        var url = this.result;
        var html_result =
            "<div class=\"png_container\">" +
            "<div class=\"png_container_content\">" +
            "<div class=\"png_content_div\">" +
            "<img class='png' id='" + name + "' src=\"" + url +"\">" +
            "</div>" +
            "<input class='png_input' type='text'>" +
            "</div>" +
            "</div>";
        $(".png_div").append(html_result);
    };

    reader.readAsDataURL(file);
}

function show_pop() {
    $("body").css("overflow", "hidden");
    $(".pop_bg")
        .css("visibility", "visible")
        .animate({
        opacity: 0.5
    }, 500, function () {
            
    });
}

function hide_pop(finish) {
    $("body").css("overflow", "auto");
    $(".pop_bg").animate({
        opacity: 0
    }, 500, function () {
        $(this).css("visibility", "hidden");
        if (finish) {
            finish();
        }
        cur_pop_selector = undefined;
        $(".pop_content").empty();
        $(".save_btn").css("visibility", "visible");
    });
}

function show_loading() {
    $("body").css("overflow", "hidden");
    $(".loading_bg")
        .css("visibility", "visible")
        .animate({
            opacity: 0.5
        }, 500, function () {

        });
    rotate_sel($(".loading"));
}

function hide_loading(finish) {
    $("body").css("overflow", "auto");
    $(".loading_bg").animate({
        opacity: 0
    }, 500, function () {
        $(this).css("visibility", "hidden");
        stop_rotate_sel($(".loading"));
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


//.....html


function subview_html() {
    var base_div = "<div class=\"base_div\">";
    var content_div = "<div class=\"content_div\">";
    var classname_btn = "<button class=\"classname_btn\">class|name<span class='tip'>(unset)</span></button>";
    var constraints_btn = "<button class=\"constraints_btn\">constraints<span class='tip'>(unset)</span></button>";
    var subviews_btn = "<button class=\"subviews_btn\">subviews</button>";
    var remove_btn = "<button class=\"remove_btn\">remove</button>";
    var end_div = "</div>";
    return base_div +
        content_div +
        classname_btn +
        constraints_btn +
        subviews_btn +
        remove_btn +
        end_div +
        end_div;
}

