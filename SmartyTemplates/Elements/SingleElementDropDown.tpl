<!--SM:if ($edit.label != "0" && $edit.label != "") || ($label != "0" && $label != ""):SM-->
<div id="<!--SM:$field:SM-->">
    <!--SM:if $edit.label != "0" && $edit.label != "":SM-->
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
    <!--SM:/if:SM-->
    <!--SM:if $label != "0" && $label != "":SM-->
        <div class="readonly<!--SM:if isset($edit.label):SM--> haseditable<!--SM:/if:SM-->">
            <label for="select_<!--SM:$field:SM-->">
                <span class="progressive_basic">Read only: </span><!--SM:$label:SM-->:
                <data id="select_<!--SM:$field:SM-->">
                    <a href="<!--SM:$SiteConfig.baseurl:SM--><!--SM:$current.element:SM-->/<!--SM:$current.key:SM-->"><!--SM:$current.value:SM--></a>
                </data>
            </label>
        </div>
    <!--SM:/if:SM-->
</div>
<!--SM:/if:SM-->