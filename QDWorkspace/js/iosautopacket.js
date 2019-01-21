/**
 * Created by zgzheng on 2019/1/13.
 */

var cur_project;
var url_base = '/Tools/IOSAutoPacket/';

var test_target_key;
var build_target_key;
var build_dic;
var version_dic;

$(document).ready(function () {
    var socket = io('http://10.10.70.232:3133');
// 当连接服务端成功时触发connect默认事件
    socket.on('connect', function(){
        console.log('connect success');
    });

    socket.on('error', function(data){
        console.log('err:',data)
    });

    socket.on('message', function(data){
        console.log('log:',data);
    });

    socket.on('add_task', function (data) {
        var task = JSON.parse(data);
        var html_result = queue_content_html(task);
        $("#queue_content").append(html_result);
        bind_task(task);
        bind_close_click();
        bind_queue_click();
    });

    socket.on('add_his', function (data) {
        var task = JSON.parse(data);
        var html_result = history_content_html(task);
        $("#history_content").prepend(html_result);

        bind_task(task);
        bind_his_click();
    });

    socket.on('update_task', function (data) {
        console.log('update' + data);
        var task = JSON.parse(data);
        bind_task(task);
        $('#' + task['task_id']).find('.ipa_status').text(task['status']);
    });

    socket.on('del_task', function (data) {
        var task_id = data;
        $("#" + task_id).remove();
    });

    socket.on('begin_task', function (data) {
        console.log('begin');
    });

    socket.on('end_loading', function (data) {
       hide_loading();
    });

    socket.on('progress', function (data) {
        var progress = Number(data);
        if (progress > 0) {
            $(".queue_cell").
                each(function () {
                    var json = $(this).data();
                    if (json['status'] != 'waiting') {

                        var progress_bg = $(this).find(".progress_bg");
                        var progress = $(this).find(".progress");
                        progress_bg.css("display", "block");
                        progress.animate({
                            width: data + '%'
                        }, 500);
                        return false;
                    }
            });

        }


        console.log('progress:' + data);
    });

    socket.on('stop task', function (data) {
        console.log('stop task:' + data);
    });

    
    get_all_task(function (data) {
        queue = $("#queue_content");
        queue.empty();
        for (idx in data) {
            task = data[idx];
            var html_result = queue_content_html(task);
            queue.append(html_result);
            bind_task(task);
        }
        bind_close_click();
        bind_queue_click();
    });

    get_all_history(function (data) {
        his = $("#history_content");
        his.empty();

        for (idx in data) {
            task = data[idx];

            var html_result = history_content_html(task);
            his.append(html_result);
            bind_task(task);
        }

        bind_his_click();
    });

    load_popview_html();
    load_loading_html();
    init_config(function (success) {
        bind_target_change();
        bind_packet_click();
    })

    $(".help").click(function () {
        show_loading();
       $.ajax({
           url : url_base + 'help',
           dataType : 'text',
           method : 'GET',
           success : function (data) {
               hide_loading();
               sel = empty_pop_content();
               show_savebtn(false);
               sel.append('<div class="detail_content"><span class="help_span">' + data + '</span></div>');
               show_pop();
           },
           error : function (error) {
               hide_loading();
           }
       })
    });
});

function bind_packet_click() {
    $("#submit").click(function () {
        add_task();
    });
}

function bind_close_click() {
    var close_sel = $(".close");
    close_sel.off('click');
    close_sel.click(function (event) {
        event.preventDefault();
        event.stopPropagation();
        var task_id = $(this).parent().attr('id');
        del_task(task_id);
    });
}

function bind_target_change() {
    $("#target").change(function () {
        $("#version").val(version_dic[$(this).val()]);
        $("#build").val(build_dic[$(this).val()]);
    });
}

function init_config(finish) {
    $("script").
    each(function () {
        if($(this).attr('src').indexOf('iosautopacket') != -1) {
            var src = $(this).attr('src');
            var query =  src.split('?').pop();
            var project_query = query.split('&')[0];
            cur_project = project_query.split('=').pop();

            get_config(finish);
        }
    });
}

function get_config(finish) {
    show_loading();
    $.ajax({
        url: url_base + 'get_config/' + cur_project,
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            hide_loading();
            test_target_key = data['test_target_key'];
            build_target_key = data['build_target_key'];
            build_dic = data['build_dic'];
            version_dic = data['version_dic'];
            finish(true);
        },
        error: function (error) {
            get_config(finish);
            // hide_loading();
            // finish(false);
        }
    })
}

function add_task() {
    show_loading();
    $.ajax({
        url: url_base + 'add_task',
        type: 'POST',
        dataType: 'text',
        data: $("form").serialize() + '&project=' + cur_project,
        success: function (data) {
        },

        error: function (error) {
            add_task();
        }
    });
}

function del_task($task_id) {
    show_loading();
    $.ajax({
        url: url_base + 'kill_task/' + $task_id,
        type: 'GET',
        dataType: 'text',
        success: function (data) {
        },

        error: function (error) {
            del_task($task_id);
        }
    });
}

function get_all_task(finish) {
    $.ajax({
        url: url_base + 'tasks' ,
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            finish(data);
        },
        error: function (error) {
            console.log(error);
        }
    });
}

function get_all_history(finish) {

    $.ajax({
        url: url_base + 'histories' ,
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            finish(data);
        },
        error: function (error) {
            console.log('get_all_his_error');
            console.log(error);
        }
    });
}



function bind_his_click() {
    var display_properties = ["status", "reason", "base_path", "ipa_name", "version", "build", "select_branch"];

    sel = $('.history_cell');
    sel.off('click');
    sel.click(function () {
        pop_sel = empty_pop_content();

        datas = $(this).data();
        for (var key in datas) {
            data = datas[key];
            if(display_properties.indexOf(key) != -1 && data != '') {
                var html = detail_html(key, data);
                pop_sel.append(html);
            }
        }

        show_savebtn(false);
        show_pop();
    });
}

function bind_queue_click() {
    sel = $('.queue_cell');
    sel.off('click');
    sel.click(function () {
        pop_sel = empty_pop_content();

        datas = $(this).data();
        for (var key in datas) {
            data = datas[key];
            if (data != '') {
                var html = detail_html(key, data);
                pop_sel.append(html);
            }
        }

        show_savebtn(false);
        show_pop();
    });
}


function bind_task(task) {
    var task_id = task['task_id'];
    $("#" + task_id).data(task);
}


function queue_content_html(task) {
    var html_result =
        "<div class='queue_cell' id='" + task['task_id'] +"'>" +
        "<span class='ipa_name'>"+ task['ipa_name'] +"</span><span class='ipa_status'>" + task['status'] + "</span>" +
        "<img class='close' src='/QDWorkspace/resource/close.png'>" +
        "<div class='progress_bg'>" +
        "<div class='progress'></div>" +
        "</div>" +
        "</div>";

    return html_result;
}


function history_content_html(task) {
    var html_result =
        "<div class='history_cell' id='" + task['task_id'] +"'>" +
            "<span class='ipa_name'>"+ task['ipa_name'] +"</span><span class='ipa_status'>" + task['status'] + "</span>" +
        "</div>";

    return html_result;
}

function detail_html(key, data) {
    var html_result =
        "<div class='detail_content'>" +
        "<span class='vp_detail'>" + key + "</span>" +
        "<span class='detail'>" + data + "</span>" +
        "</div>";

    return html_result;
}