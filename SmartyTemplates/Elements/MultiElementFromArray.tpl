<div id="<!--SM:$field:SM-->">
    <!--SM:if isset($edit.label):SM-->
        <div class="readwrite">
            <span class="progressive_basic">Read write: </span><!--SM:$edit.label:SM-->:
                <!--SM:foreach $list as $item:SM-->
                    <!--SM:if !isset($exclude) || ($item.current.key != $exclude):SM-->
                        <div>
                            <label for='<!--SM:$field:SM-->_del_<!--SM:$item.current.key:SM-->'>
                                Disassociate <!--SM:$item.current.value:SM-->? 
                                <input type='checkbox' name='<!--SM:$field:SM-->[]' value='del_<!--SM:$item.current.key:SM-->' id='<!--SM:$field:SM-->_del_<!--SM:$item.current.key:SM-->' />
                            </label>
                        </div>
                    <!--SM:/if:SM-->
                <!--SM:/foreach:SM-->
                <div>
                    <label for='<!--SM:$field:SM-->_new'>Add: 
                        <select name="<!--SM:$field:SM-->[]" id='<!--SM:$field:SM-->_new'>
                            <option value="">None</option>
                            <!--SM:foreach $edit.list as $item:SM-->
                                <!--SM:if !isset($exclude) || ($item@key != $exclude):SM-->
                                    <option value="<!--SM:$item@key:SM-->"><!--SM:$item:SM--></option>
                                <!--SM:/if:SM-->
                            <!--SM:/foreach:SM-->
                        </select>            
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
                            <li><a href="<!--SM:$SiteConfig.baseurl:SM--><!--SM:$item.current.element:SM-->/<!--SM:$item.current.key:SM-->"><!--SM:$item.current.value:SM--></a></li>
                        </ul>
                    <!--SM:/foreach:SM-->
                </data>
            </label>
        </div>
    <!--SM:/if:SM-->
</div>