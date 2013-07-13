<!--SM:if isset($dodebug) && $dodebug == "true":SM--><pre><!--SM:var_dump("edit", $edit, "label", $label, "field", $field, "list", $list, "current", $current):SM--></pre><!--SM:/if:SM-->
<!--SM:if !isset($placeholder):SM--><!--SM:$placehlder = "":SM--><!--SM:/if:SM-->
<div id="<!--SM:$field:SM-->">
    <!--SM:if isset($edit.label):SM-->
        <div class="readwrite">
            <span class="progressive_basic">Read write: </span><!--SM:$edit.label:SM-->:
                <!--SM:foreach $list as $item:SM-->
                    <!--SM:if !isset($exclude) || ($item@key != $exclude):SM-->
                        <div>
                            <label for='<!--SM:$field:SM-->_del_<!--SM:$item@key:SM-->'>
                                Disassociate <!--SM:$item:SM-->? 
                                <input type='checkbox' name='<!--SM:$field:SM-->[]' value='del_<!--SM:$item@key:SM-->' id='<!--SM:$field:SM-->_del_<!--SM:$item@key:SM-->' />
                            </label>
                        </div>
                    <!--SM:/if:SM-->
                <!--SM:/foreach:SM-->
                <div>
                    <label for='<!--SM:$field:SM-->_new'>Add: 
                        <input type="text" name="<!--SM:$field:SM-->[]"  placeholder="<!--SM:$placeholder:SM-->" id="<!--SM:$field:SM-->_new" />
                    </label>
                </div>
            </label>
        </div>
    <!--SM:/if:SM-->
    <!--SM:if isset($label) && count($list) > 0:SM-->
        <div class="readonly<!--SM:if isset($edit.label):SM--> haseditable<!--SM:/if:SM-->">
            <label for="<!--SM:$field:SM-->">
                <span class="progressive_basic">Read only: </span><!--SM:$label:SM-->:
                <data id="<!--SM:$field:SM-->">
                    <!--SM:foreach $list as $item:SM-->
                        <ul>
                            <!--SM:if 0 + $item@key > 0 || $item@key == "0":SM-->
                                <li><a href="<!--SM:$item:SM-->"><!--SM:$item:SM--></a></li>
                            <!--SM:elseif !isset($current):SM-->
                                <li><a href="<!--SM:$item:SM-->"><!--SM:$item@key:SM--></a></li>
                            <!--SM:else:SM-->
                                <li><a href="<!--SM:$SiteConfig.baseurl:SM--><!--SM:$current:SM-->/<!--SM:$item@key:SM-->"><!--SM:$item:SM--></a></li>
                            <!--SM:/if:SM-->    
                        </ul>
                    <!--SM:/foreach:SM-->
                </data>
            </label>
        </div>
    <!--SM:/if:SM-->
</div>