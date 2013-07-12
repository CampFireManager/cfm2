<!--SM:if isset($dodebug) && $dodebug == "true":SM--><pre><!--SM:var_dump("edit", $edit, "label", $label, "field", $field, "current", $current):SM--></pre><!--SM:/if:SM-->
<!--SM:if !isset($placeholder):SM--><!--SM:$placehlder = "":SM--><!--SM:/if:SM-->
<!--SM:if (isset($edit.label) && $edit.label != "0" && $edit.label != "") || (isset($label) && $label != "0" && $label != ""):SM-->
<div id="<!--SM:$field:SM-->">
    <!--SM:if isset($edit.label) && $edit.label != "0" && $edit.label != "":SM-->
        <div class="readwrite">
            <label for="edit_text_<!--SM:$field:SM-->">
                <span class="progressive_basic">Read write: </span><!--SM:$edit.label:SM-->:
                <input type="text" name="<!--SM:$field:SM-->"  placeholder="<!--SM:$placeholder:SM-->" id="edit_text_<!--SM:$field:SM-->" value="<!--SM:$current:SM-->">
            </label>
        </div>
    <!--SM:/if:SM-->
    <!--SM:if isset($label) && $label != "0" && $label != "":SM-->
        <div class="readonly<!--SM:if isset($edit.label):SM--> haseditable<!--SM:/if:SM-->">
            <label for="text_<!--SM:$field:SM-->">
                <span class="progressive_basic">Read only: </span><!--SM:$label:SM-->:
                <data id="text_<!--SM:$field:SM-->">
                      <!--SM:$current:SM-->
                </data>
            </label>
        </div>
    <!--SM:/if:SM-->
</div>
<!--SM:/if:SM-->
