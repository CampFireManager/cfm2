<!--SM:if (isset($edit.label) && $edit.label != "0") || (isset($label) && $label != "0"):SM-->
<div id="<!--SM:$field:SM-->">
    <!--SM:if isset($edit.label) && $edit.label != "0" && $edit.label != "":SM-->
        <div class="readwrite">
            <label for="edit_boolean_<!--SM:$field:SM-->">
                <span class="progressive_basic">Read write: </span><!--SM:$edit.label:SM-->:
                <select name="<!--SM:$field:SM-->" id="edit_boolean_<!--SM:$field:SM-->" data-role="slider">
                    <option value="0"<!--SM:if ($current == null && $edit.default_value == 0) || $current == 0:SM--> selected="selected"<!--SM:/if:SM-->>No</option>
                    <option value="1"<!--SM:if ($current == null && $edit.default_value == 1) || $current != 0:SM--> selected="selected"<!--SM:/if:SM-->>Yes</option>
                </select>
            </label>
        </div>
    <!--SM:/if:SM-->
    <!--SM:if isset($label) && $label != "0" && $label != "":SM-->
        <div class="readonly<!--SM:if isset($edit.label):SM--> haseditable<!--SM:/if:SM-->">
            <label for="boolean_<!--SM:$field:SM-->">
                <span class="progressive_basic">Read only: </span><!--SM:$label:SM-->:
                <data id="boolean_<!--SM:$field:SM-->">
                    <!--SM:if $current != 0:SM-->
                            Yes
                    <!--SM:else:SM-->
                    No
                    <!--SM:/if:SM-->
                </data>
            </label>
        </div>
    <!--SM:/if:SM-->
</div>
<!--SM:/if:SM-->
