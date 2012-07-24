{include file="header.tpl" title="Timetable"}
{foreach $Collection_TimetablePortrait as $Timetable}
<table>
    <tr>
        <th>&nbsp;</th>
{if $Timetable.x_axis == 'room'}
{assign var="xaxis" value=$Timetable.arrRooms}
{assign var="yaxis" value=$Timetable.arrSlots}
{else}
{assign var="xaxis" value=$Timetable.arrSlots}
{assign var="yaxis" value=$Timetable.arrRooms}
{/if}
{foreach $xaxis as $xaxis_value}
{if $Timetable.x_axis == 'room'}
        <th class="room" id="{$xaxis_value@key}">Room {$xaxis_value.intRoomID} {$xaxis_value.strRoom}</th>
{else}
        <th class="slot{if isset($xaxis_value.isNow) && $xaxis_value.isNow} slot_now{elseif isset($xaxis_value.isNext) && $xaxis_value.isNext} slot_next{/if}" id="{$xaxis_value@key}">
            Slot {$xaxis_value.intSlotID} {$xaxis_value.timeStart}-{$xaxis_value.timeEnd}
        </th>
{/if}
{/foreach}
    </tr>

{foreach $yaxis as $yaxis_value}
{if $Timetable.y_axis == 'room'}
    <tr class="room" id="{$yaxis_value@key}">
        <th class="room" id="{$yaxis_value@key}">Room {$yaxis_value.intRoomID} {$yaxis_value.strRoom}</th>
{else}
    <tr class="slot{if isset($yaxis_value.isNow) && $yaxis_value.isNow} slot_now{elseif isset($yaxis_value.isNext) && $yaxis_value.isNext} slot_next{/if}" id="{$yaxis_value@key}">
        <th class="slot{if isset($yaxis_value.isNow) && $yaxis_value.isNow} slot_now{elseif isset($yaxis_value.isNext) && $yaxis_value.isNext} slot_next{/if}" id="{$yaxis_value@key}">
            Slot {$yaxis_value.intSlotID} {$yaxis_value.timeStart}-{$yaxis_value.timeEnd}
        </th>
{/if}
{foreach $xaxis as $xaxis_value}
        <td class="talk{if $Timetable.arrTimetable[$xaxis_value@key][$yaxis_value@key]['isNow'] == 1} slot_now{elseif $Collection_Timetable.0.arrTimetable[$xaxis_value@key][$yaxis_value@key]['isNext'] == 1} slot_next{/if}">
{include file="Timetable_TalkCell.tpl" cell=$Timetable.arrTimetable[$xaxis_value@key][$yaxis_value@key]}
        </td>
{/foreach}
    </tr>
{/foreach}
</table>
{/foreach}
{include file="footer.tpl"}