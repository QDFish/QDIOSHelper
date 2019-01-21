/**
 * Created by zgzheng on 2019/1/20.
 */
$(document).ready(function () {
   $("#view_helper").click(function () {
       location.href= "/Tools/IOSViewModel/";
   });

    $("#data_helper").click(function () {
        location.href= "/Tools/IOSDataModel/";
    });

    $("#packet_choose").hide();

    $("#packet_helper").hover(function () {
        $("#packet_choose").show();
    }, function () {
        $("#packet_choose").hide();
    });

    $(".packet_cell").click(function () {
        id = $(this).attr('id');
        location.href= "/Tools/IOSAutoPacket/index/" + id;
    });
});