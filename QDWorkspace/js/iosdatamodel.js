/**
 * Created by zgzheng on 2019/1/7.
 */
$(document).ready(function () {
    bind_json_change();
});

var cur_char = 65;

function bind_json_change() {
    var old_val = "";
    $("#input").on("change keyup paste", function () {
        var current_val = $(this).val();
        if(current_val == old_val) {
            return;
        }
        old_val = current_val;

        if (str_is_json(current_val)) {
            cur_char = 65;
            $("#protocol_div").empty();
            $("#interface_div").empty();
            $("#implement_div").empty();
            deal_json(JSON.parse(current_val), cur_char);
            $("#tip").text("");
        } else {
            $("#tip").text("wrong json");
        }
    });
}

function deal_json(json) {
    if (json_is_dic(json)) {
        $("#protocol_div").append("<span class='protocol_span'>@protocol " + current_class() + ";</span>");
        $("#interface_div").append(
            "<span class='interface_span'>@interface " + current_class() + " : NSObject</span>" +
                "<div class='property_div' id='" + String.fromCharCode(cur_char) +"'></div>" +
            "<span class='interface_span'>@end</span>"
        );
        $("#implement_div").append(
            "<span class='implementation_span'>@implementation " + current_class() +"</span>" +
            "<span class='implementation_span'>@end</span></br>"
        );

        for (key in json) {
            value = json[key];
            var val_name = remove_underline(key);
            var cur_property_div = $("#" + String.fromCharCode(cur_char));
            
            
            if (json_is_dic(value)) {
                cur_char++;
                cur_property_div.append("<span class='property_span'>@property (nonatomic, strong)" + current_class() + ' *' + val_name + ";</span>");
                deal_json(value);
            } 
            
            else if(json_is_array(value)) {
                var protocol = "";
                if(Object.keys(value).length > 0 && json_is_dic(value[0])) {
                    cur_char++;
                    protocol = current_class();
                    deal_json(value[0]);
                }

                cur_property_div.append("<span class='property_span'>@property (nonatomic, copy) NSArray" + protocol + ' *' + val_name + ";</span>");

            }
            
            else {
                cur_property_div.append("<span class='property_span'>@property (nonatomic, copy) NSString" + ' *' + val_name + ";</span>");
            }
        }
    }
}

function remove_underline(val) {
    var val_arr = val.split("_");
    var val_new_arr = [val_arr[0]];
    for (var i = 1; i < Object.keys(val_arr).length; i ++) {
        var new_val = val_arr[i].toLowerCase().replace(/( |^)[a-z]/g, (L) => L.toUpperCase());
        val_new_arr.push(new_val);
    }
    return val_new_arr.join("");
}

//

function current_class() {
    return "<#" + String.fromCharCode(cur_char) + "class#>"
}

function str_is_json(str) {
    try {
        if (typeof JSON.parse(str) == "object") {
            return true;
        }
    } catch (e) {

    }

    return false;
}

function json_is_array(json) {
    if (typeof json != 'object') {
        return false;
    }


    for (var key in json) {
        if (key == 0) {
            return true;
        } else if (typeof key == "string") {
            return false;
        }
    }


    return false;
}

function json_is_dic(json) {
    if (typeof json != 'object') {
        return false;
    }

    for (var key in json) {
        if (key == 0) {
            return false;
        } else if (typeof key == "string") {
            return true;
        }
    }

    return false;
}

function ShowTheObject(obj) {
    var des = "";
    for (var name in obj) {
        des += name + ":" + obj[name] + ";\n";
    }
    alert(des);
}