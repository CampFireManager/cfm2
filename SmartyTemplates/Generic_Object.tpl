<!--SM:assign var=comma value=',':SM-->
<!--SM:foreach $renderPage as $object:SM-->
    <div id="Item_<!--SM:$object@key:SM-->">
    <!--SM:foreach $object.labels as $label:SM-->
        <!--SM:if (is_array($object[$label@key])):SM-->
            <div>
                <!--SM:$label:SM-->: 
                <data id="<!--SM:$label@key:SM-->_<!--SM:$object@key:SM-->">
                    <!--SM:foreach $object[$label@key] as $member:SM-->
                        <!--SM:$member:SM-->
                    <!--SM:/foreach:SM-->
                </data>
            </div>
        <!--SM:elseif ($object[$label@key] != ''):SM-->
            <div><!--SM:$label:SM-->: <data id="<!--SM:$label@key:SM-->_<!--SM:$object@key:SM-->"><!--SM:$object[$label@key]:SM--></data></div>
        <!--SM:/if:SM-->
    <!--SM:/foreach:SM-->
    <!--SM:foreach $object as $element:SM-->
        <!--SM:if ($element@key != 'labels' && is_array($element) && isset($element.labels)):SM-->
            <!--SM:foreach $element.labels as $label:SM-->
                <!--SM:if (is_array($element[$label@key])):SM-->
                    <div>
                        <!--SM:$label:SM-->: 
                        <data id="<!--SM:$label@key:SM-->_<!--SM:$element@key:SM-->">
                            <!--SM:foreach $element[$label@key] as $member:SM-->
                                <!--SM:$member:SM-->
                            <!--SM:/foreach:SM-->
                        </data>
                    </div>
                <!--SM:elseif ($element[$label@key] != ''):SM-->
                    <div><!--SM:$label:SM-->: <data id="<!--SM:$label@key:SM-->_<!--SM:$element@key:SM-->"><!--SM:$element[$label@key]:SM--></data></div>
                <!--SM:/if:SM-->
            <!--SM:/foreach:SM-->
        <!--SM:/if:SM-->
    <!--SM:/foreach:SM-->
    </div>
<!--SM:if !$object@last:SM--><hr /><!--SM:/if:SM-->
<!--SM:/foreach:SM-->