<!--SM:if isset($dodebug) && $dodebug == "true":SM--><pre><!--SM:var_dump("edit", $edit, "field", $field, "current", $current):SM--></pre><!--SM:/if:SM-->
<!--SM:if $edit.label != "0" && $edit.label != "":SM-->
    <div id="<!--SM:$field:SM-->">
        <div class="readwrite">
            <label for="edit_<!--SM:$field:SM-->">
                <span class="progressive_basic">Read write: </span><!--SM:$edit.label:SM-->:
                <select name="<!--SM:$field:SM-->" id="edit_<!--SM:$field:SM-->">
                    <!--SM:foreach $edit.list as $item:SM-->
                        <option value="<!--SM:$item@key:SM-->"<!--SM:if $current.key == $item@key:SM--> selected="selected"<!--SM:/if:SM-->><!--SM:$item:SM--></option>
                    <!--SM:/foreach:SM-->
                </select>
            </label>
        </div>
    </div>
<!--SM:else:SM-->
        <input type="hidden" name="<!--SM:$field:SM-->" value="<!--SM:$current.key:SM-->" />
<!--SM:/if:SM-->