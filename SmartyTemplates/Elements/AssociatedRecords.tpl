<div id="<!--SM:$field:SM-->">
    <!--SM:if isset($label) && count($list) > 0:SM-->
        <div class="readonly">
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