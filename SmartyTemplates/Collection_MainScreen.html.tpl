<html>
<head>
<title></title>
<link rel="stylesheet" href="media/css.css" type="text/css">
<link rel="stylesheet" href="media/JQM/jquery.mobile-1.1.1.min.css">
<script src="media/JQM/jquery-1.7.1.min.js"></script>
<script src="media/JQM/jquery.mobile-1.1.1.min.js"></script>
</head>
<body>
<!--SM:foreach $Collection_MainScreen as $Timetable:SM-->
<div data-role="page">
<div data-role="content">
<table>
    <tr>
        <th>&nbsp;</th>
<!--SM:if $Timetable.x_axis == 'room':SM-->
<!--SM:assign var="xaxis" value=$Timetable.arrRooms:SM-->
<!--SM:assign var="yaxis" value=$Timetable.arrSlots:SM-->
<!--SM:else:SM-->
<!--SM:assign var="xaxis" value=$Timetable.arrSlots:SM-->
<!--SM:assign var="yaxis" value=$Timetable.arrRooms:SM-->
<!--SM:/if:SM-->
<!--SM:foreach $xaxis as $xaxis_value:SM-->
<!--SM:if $Timetable.x_axis == 'room':SM-->
        <th class="room" id="<!--SM:$xaxis_value@key:SM-->"><!--SM:$xaxis_value.strRoom:SM--></th>
<!--SM:else:SM-->
        <th class="slot<!--SM:if isset($xaxis_value.isNow) && $xaxis_value.isNow:SM--> slot_now<!--SM:elseif isset($xaxis_value.isNext) && $xaxis_value.isNext:SM--> slot_next<!--SM:/if:SM-->" id="<!--SM:$xaxis_value@key:SM-->">
           <!--SM:$xaxis_value.timeStart:SM-->-<!--SM:$xaxis_value.timeEnd:SM-->
        </th>
<!--SM:/if:SM-->
<!--SM:/foreach:SM-->
    </tr>

<!--SM:foreach $yaxis as $yaxis_value:SM-->
<!--SM:if $Timetable.y_axis == 'room':SM-->
    <tr class="room" id="<!--SM:$yaxis_value@key:SM-->">
        <th class="room" id="<!--SM:$yaxis_value@key:SM-->"><!--SM:$yaxis_value.strRoom:SM--></th>
<!--SM:else:SM-->
    <tr class="slot<!--SM:if isset($yaxis_value.isNow) && $yaxis_value.isNow:SM--> slot_now<!--SM:elseif isset($yaxis_value.isNext) && $yaxis_value.isNext:SM--> slot_next<!--SM:/if:SM-->" id="<!--SM:$yaxis_value@key:SM-->">
        <th class="slot<!--SM:if isset($yaxis_value.isNow) && $yaxis_value.isNow:SM--> Now<!--SM:elseif isset($yaxis_value.isNext) && $yaxis_value.isNext:SM--> Next<!--SM:/if:SM-->" id="<!--SM:$yaxis_value@key:SM-->">
            Slot <!--SM:$yaxis_value.intSlotID:SM--> <!--SM:$yaxis_value.timeStart:SM-->-<!--SM:$yaxis_value.timeEnd:SM-->
        </th>
<!--SM:/if:SM-->
<!--SM:foreach $xaxis as $xaxis_value:SM-->
        <td class="talk <!--SM:if $Timetable.arrTimetable[$yaxis_value@key][$xaxis_value@key]['isNow'] == 1:SM-->slot_now<!--SM:elseif $Timetable.arrTimetable[$yaxis_value@key][$xaxis_value@key]['isNext'] == 1:SM-->slot_next<!--SM:/if:SM-->">
<!--SM:include file="Timetable_TalkCell.tpl" cell=$Timetable.arrTimetable[$yaxis_value@key][$xaxis_value@key]:SM-->
        </td>
<!--SM:/foreach:SM-->
    </tr>
<!--SM:/foreach:SM-->
</table>
<!--SM:/foreach:SM-->
</div>
</div>
</body>
</html>
