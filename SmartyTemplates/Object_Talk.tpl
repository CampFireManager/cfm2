<!--SM:if 0 + substr($SiteConfig.thisurl, -1) > 0 || 0 + substr($SiteConfig.thisurl, -2, 1) > 0 || substr($SiteConfig.thisurl, -2) == 'me' || substr($SiteConfig.thisurl, -3) == 'me/':SM-->
    <!--SM:if isset($object.isEditable) && count($object.isEditable) > 0:SM-->
        
        <form action="<!--SM:$SiteConfig.thisurl:SM-->" method="post">
    <!--SM:/if:SM--><!-- This is editable - add the form tags -->
<!--SM:/if:SM--><!-- this is an actual object, not the whole data set -->

<div id="intTalkID">
    <div><label>
        Talk ID:
        <data>
            <!--SM:$object.intTalkID:SM-->
        </data>
    </label></div>
</div>
<div id="strTalk">
    <div><label class="readonly">
        <!--SM:$object.labels.strTalk:SM-->: 
        <data>
            <!--SM:$object.strTalk:SM-->
        </data>
    </label></div>
<!--SM:if isset($object.isEditable.strTalk):SM-->
    <div><label class="readwrite">
        <!--SM:$object.isEditable.strTalk.label:SM-->: 
        <data>
            <input type="text" name="strTalk" value="<!--SM:$object.strTalk:SM-->" />
        </data>
    </label></div>
<!--SM:/if:SM-->
</div>
<div id="strTalkSummary">
    <div><label class="readonly">
        <!--SM:$object.labels.strTalkSummary:SM-->: 
        <data>
            <!--SM:$object.strTalkSummary:SM-->
        </data>
    </label></div>
<!--SM:if isset($object.isEditable.strTalkSummary):SM-->
    <div><label class="readwrite">
        <!--SM:$object.isEditable.strTalkSummary.label:SM-->: 
        <data>
            <input type="text" name="strTalkSummary" value="<!--SM:$object.strTalkSummary:SM-->" />
        </data>
    </label></div>
<!--SM:/if:SM-->
</div>
<!--SM:if ($object.hasNsfwMaterial != null && $object.hasNsfwMaterial != 0) || isset($object.isEditable.hasNsfwMaterial):SM-->
<div id="hasNsfwMaterial">
    <div><label class="readonly">
        <!--SM:$object.labels.hasNsfwMaterial:SM-->: 
        <data>
            <!--SM:if ($object.hasNsfwMaterial == null && $object.isEditable.hasNsfwMaterial.default_value == 1) || $object.hasNsfwMaterial != 0:SM-->:SM-->
                Yes
            <!--SM:else:SM-->
                No
            <!--SM:/if:SM-->
        </data>
    </label></div>
<!--SM:if isset($object.isEditable.hasNsfwMaterial):SM-->
    <div><label class="readwrite">
        <!--SM:$object.isEditable.hasNsfwMaterial.label:SM-->: 
        <data>
            <select name="hasNsfwMaterial">
                <option value="0"<!--SM:if ($object.hasNsfwMaterial == null && $object.isEditable.hasNsfwMaterial.default_value == 0) || $object.hasNsfwMaterial == 0:SM--> selected="selected"<!--SM:/if:SM-->>No</option>
                <option value="1"<!--SM:if ($object.hasNsfwMaterial == null && $object.isEditable.hasNsfwMaterial.default_value == 1) || $object.hasNsfwMaterial != 0:SM--> selected="selected"<!--SM:/if:SM-->>Yes</option>
            </select>
        </data>
    </label></div>
<!--SM:/if:SM-->
</div>
<!--SM:/if:SM-->
<div id="intSlotID">
    <div><label class="readonly">
        <!--SM:$object.labels.intSlotID:SM-->: 
        <data>
            <!--SM:if date('Y-m-d') != $object.arrSlot.dateStart:SM--><!--SM:$object.arrSlot.dateStart:SM--><!--SM:/if:SM-->
            <!--SM:$object.arrSlot.timeStart:SM--> - <!--SM:$object.arrSlot.timeEnd:SM-->
        </data>
    </label></div>
<!--SM:if isset($object.isEditable.intSlotID):SM-->
    <div><label class="readwrite">
        <!--SM:$object.isEditable.intSlotID.label:SM-->: 
        <data>
            <select name="intSlotID">
                <!--SM:foreach $object.isEditable.intSlotID.list as $slot:SM-->
                    <option value="<!--SM:$slot@key:SM-->"<!--SM:if $object.intSlotID != $slot@key:SM--> selected="selected"<!--SM:/if:SM-->><!--SM:$slot:SM--></option>
                <!--SM:/foreach:SM-->
            </select>            
        </data>
    </label></div>
<!--SM:/if:SM-->
</div>
<div id="intLength">
    <div><label class="readonly">
        <!--SM:$object.labels.intLength:SM-->: 
        <data>
            <!--SM:$object.intLength:SM-->
        </data>
    </label></div>
<!--SM:if isset($object.isEditable.intLength):SM-->
    <div><label class="readwrite">
        <!--SM:$object.isEditable.intLength.label:SM-->: 
        <data class="readwrite">
            <input type="text" name="intLength" value="<!--SM:$object.intLength:SM-->" />
        </data>
    </label></div>
<!--SM:/if:SM-->
</div>
<div id="jsonLinks">
    <div><label class="readonly">
        <!--SM:$object.labels.jsonLinks:SM-->: 
        <data>
            <!--SM:foreach $object.arrLinks as $strLink:SM-->
                <!--SM:if !is_numeric($strLink@key) && $strLink@key != '':SM--><a href="<!--SM:$strLink:SM-->"><!--SM:$strLink@key:SM--></a><!--SM:else:SM--><a href="<!--SM:$strLink:SM-->"><!--SM:$strLink:SM--></a><!--SM:/if:SM--><!--SM:if !$strLink@last:SM-->, <!--SM:/if:SM-->
            <!--SM:/foreach:SM-->
        </data>
    </label></div>
<!--SM:if isset($object.isEditable.jsonLinks):SM-->
    <!--SM:$object.isEditable.jsonLinks.label:SM-->: 
    <div class="readwrite">
        <!--SM:foreach $object.arrLinks as $strLink:SM-->
            <!--SM:if !is_numeric($strLink@key) && $strLink@key != '':SM-->
                <div><label>Remove <!--SM:$strLink@key:SM--> (<!--SM:$strLink:SM-->)? <input type='checkbox' name='jsonLinks[]' value='del_<!--SM:$strLink:SM-->' /></label></div>
            <!--SM:else:SM-->
                <div><label>Remove <!--SM:$strLink:SM-->? <input type='checkbox' name='jsonLinks[]' value='del_<!--SM:$strLink:SM-->' /></label></div>
            <!--SM:/if:SM-->
        <!--SM:/foreach:SM-->
        <div><label>Add: <input type="text" name='jsonLinks[]' value='' /></label></div>
    </div>
<!--SM:/if:SM-->
</div>
<!--SM:if isset($object.isEditable.intUserID):SM-->
<div id="intUserID">
    <div><label class="readwrite">
        <!--SM:$object.isEditable.intUserID.label:SM-->: 
        <data>
            <select name="intUserID">
                <!--SM:foreach $object.isEditable.intUserID.list as $user:SM-->
                    <option value="<!--SM:$user@key:SM-->"<!--SM:if $object.intUserID == $user@key:SM--> selected="selected"<!--SM:/if:SM-->><!--SM:$user:SM--></option>
                <!--SM:/foreach:SM-->
            </select>            
        </data>
    </label></div>
</div>
<!--SM:/if:SM-->
<!--SM:if isset($object.isEditable.intRoomID):SM-->
<div id="intRoomID">
    <div><label class="readwrite">
        <!--SM:$object.isEditable.intRoomID.label:SM-->: 
        <data>
            <select name="intRoomID">
                <option value="-1"<!--SM:if $object.intRoomID == -1:SM--> selected="selected"<!--SM:/if:SM-->>Dynamic Sorting</option>
                <!--SM:foreach $object.isEditable.intRoomID.list as $room:SM-->
                    <option value="<!--SM:$room@key:SM-->"<!--SM:if $object.intRoomID == $room@key:SM--> selected="selected"<!--SM:/if:SM-->><!--SM:$room:SM--></option>
                <!--SM:/foreach:SM-->
            </select>            
        </data>
    </label></div>
</div>
<!--SM:/if:SM-->
<!--SM:if ($object.isLocked != null && $object.isLocked != 0) || isset($object.isEditable.isLocked):SM-->
<div id="isLocked">
    <div><label class="readonly">
        <!--SM:$object.labels.isLocked:SM-->: 
        <data>
            <!--SM:if ($object.isLocked == null && $object.isEditable.isLocked.default_value == 1) || $object.isLocked != 0:SM-->
                Yes
            <!--SM:else:SM-->
                No
            <!--SM:/if:SM-->
        </data>
    </label></div>
<!--SM:if isset($object.isEditable.isLocked):SM-->
    <div><label class="readwrite">
        <!--SM:$object.isEditable.isLocked.label:SM-->: 
        <data>
            <select name="isLocked">
                <option value="0"<!--SM:if ($object.isLocked == null && $object.isEditable.isLocked.default_value == 0) || $object.isLocked == 0:SM--> selected="selected"<!--SM:/if:SM-->>No</option>
                <option value="1"<!--SM:if ($object.isLocked == null && $object.isEditable.isLocked.default_value == 1) || $object.isLocked != 0:SM--> selected="selected"<!--SM:/if:SM-->>Yes</option>
            </select>
        </data>
    </label></div>
<!--SM:/if:SM-->
</div>
<!--SM:/if:SM-->
<div id="jsonOtherPresenters">
    <div><label class="readonly">
        <!--SM:$object.labels.jsonOtherPresenters:SM-->: 
        <data>
            <!--SM:foreach $object.arrPresenters as $arrPresenter:SM-->
                <a href="<!--SM:$SiteConfig.baseurl:SM-->user/<!--SM:$arrPresenter.intUserID:SM-->"><!--SM:$arrPresenter.strUser:SM--></a><!--SM:if !$arrPresenter@last:SM-->, <!--SM:/if:SM-->
            <!--SM:/foreach:SM-->
        </data>
    </label></div>
<!--SM:if isset($object.isEditable.jsonOtherPresenters):SM-->
    <div class="readwrite">
        <!--SM:$object.isEditable.jsonOtherPresenters.label:SM-->: 
        <!--SM:foreach $object.arrPresenters as $arrPresenter:SM-->
            <!--SM:if $arrPresenter.intUserID != $object.intUserID:SM-->
                <div><label>Disassociate <!--SM:$arrPresenter.strUser:SM-->? <input type='checkbox' name='jsonOtherPresenters[]' value='del_<!--SM:$arrPresenter.intUserID:SM-->' /></label></div>
            <!--SM:/if:SM-->
        <!--SM:/foreach:SM-->
        <div><label>
            Add: 
            <select name="jsonOtherPresenters[]">
                <option value="">None</option>
                <!--SM:foreach $object.isEditable.jsonOtherPresenters.list as $presenter:SM-->
                    <!--SM:if $presenter@key != $object.intUserID:SM-->
                        <option value="<!--SM:$presenter@key:SM-->"><!--SM:$presenter:SM--></option>
                    <!--SM:/if:SM-->
                <!--SM:/foreach:SM-->
            </select>            
        </label></div>
    </div>
<!--SM:/if:SM-->
</div>
<!--SM:if 0 + substr($SiteConfig.thisurl, -1) > 0 || 0 + substr($SiteConfig.thisurl, -2, 1) > 0 || substr($SiteConfig.thisurl, -2) == 'me' || substr($SiteConfig.thisurl, -3) == 'me/':SM-->
    <!--SM:if isset($object.isEditable) && count($object.isEditable) > 0:SM-->
        <input type="submit" value="Update" />
        </form>
    <!--SM:/if:SM--><!-- This is editable - add the form tags -->
<!--SM:/if:SM--><!-- this is an actual object, not the whole data set -->