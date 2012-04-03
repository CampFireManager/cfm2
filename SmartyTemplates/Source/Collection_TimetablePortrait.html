{include file="header.tpl" title="Timetable"}
<table>
    <tr>
        <th>&nbsp;</th>
{if $Collection_TimetablePortrait.0.x_axis == 'room'}
{assign var="xaxis" value=$Collection_TimetablePortrait.0.arrRooms}
{assign var="yaxis" value=$Collection_TimetablePortrait.0.arrSlots}
{else}
{assign var="xaxis" value=$Collection_TimetablePortrait.0.arrSlots}
{assign var="yaxis" value=$Collection_TimetablePortrait.0.arrRooms}
{/if}
{foreach $xaxis as $xaxis_value}
{if $Collection_TimetablePortrait.0.x_axis == 'room'}
        <th class="room" id="{$xaxis_value@key}">Room {$xaxis_value.intRoomID} {$xaxis_value.strRoomName}</th>
{else}
        <th class="slot{if isset($xaxis_value.isNow) && $xaxis_value.isNow} slot_now{elseif isset($xaxis_value.isNext) && $xaxis_value.isNext} slot_next{/if}" id="{$xaxis_value@key}">
            Slot {$xaxis_value.intSlotID} {$xaxis_value.timeStart}-{$xaxis_value.timeEnd}
        </th>
{/if}
{/foreach}
    </tr>

{foreach $yaxis as $yaxis_value}
{if $Collection_TimetablePortrait.0.y_axis == 'room'}
    <tr class="room" id="{$yaxis_value@key}">
        <th class="room" id="{$yaxis_value@key}">Room {$yaxis_value.intRoomID} {$yaxis_value.strRoomName}</th>
{else}
    <tr class="slot{if isset($yaxis_value.isNow) && $yaxis_value.isNow} slot_now{elseif isset($yaxis_value.isNext) && $yaxis_value.isNext} slot_next{/if}" id="{$yaxis_value@key}">
        <th class="slot{if isset($yaxis_value.isNow) && $yaxis_value.isNow} slot_now{elseif isset($yaxis_value.isNext) && $yaxis_value.isNext} slot_next{/if}" id="{$yaxis_value@key}">
            Slot {$yaxis_value.intSlotID} {$yaxis_value.timeStart}-{$yaxis_value.timeEnd}
        </th>
{/if}
{foreach $xaxis as $xaxis_value}
        <td class="talk">
{include file="Timetable_TalkCell.tpl" cell=$Collection_TimetablePortrait.0.arrTimetable[$xaxis_value@key][$yaxis_value@key]}
        </td>
{/foreach}
    </tr>
{/foreach}
</table>
{include file="footer.tpl"}