<!--SM:assign var=comma value=',':SM-->
<!--SM:foreach $renderPage as $object:SM-->
    <!--SM:if 0 + substr($SiteConfig.thisurl, -1) > 0 && isset($object.isEditable) && count($object.isEditable) > 0:SM-->
        <form action="<!--SM:$SiteConfig.thisurl:SM-->" method="post">
    <!--SM:/if:SM-->
        <div id="Item_<!--SM:$object@key:SM-->">
            <!-- An Object has three element types -->
            <!--SM:foreach $object as $element:SM-->
                <!-- Item 1 - directly addressed items from the database (editable) -->
                <!--SM:if !is_array($element) && 0 + substr($SiteConfig.thisurl, -1) > 0 && isset($object.isEditable[$element@key]):SM-->
                    <div id="E_<!--SM:$object@key:SM-->_<!--SM:$element@key:SM-->">DE: 
                        <label>
                        <!--SM:$object.labels[$element@key]:SM-->
                        <!--SM:if isset($object.isEditable[$element@key].required):SM-->
                            <span class="required">(required)</span>
                        <!--SM:/if:SM-->
                        <!--SM:if isset($object.isEditable[$element@key].list):SM-->
                            <select id="<!--SM:$element@key:SM-->" name="<!--SM:$element@key:SM-->">
                            <!--SM:if isset($object.isEditable[$element@key].optional):SM-->
                                <option value="">N/A</option>
                            <!--SM:/if:SM-->
                            <!--SM:foreach $object.isEditable[$element@key].list as $listitem:SM-->
                                <option value="<!--SM:$listitem@key:SM-->" <!--SM:if isset($value.default_value) && $value.default_value == $listitem@key:SM--> selected="selected"<!--SM:/if:SM-->><!--SM:$listitem:SM--></option>
                            <!--SM:/foreach:SM-->
                            </select>
                        <!--SM:else:SM-->
                            <input type="<!--SM:if isset($listitem.input_type):SM--><!--SM:$listitem.input_type:SM--><!--SM:else:SM-->text<!--SM:/if:SM-->" id="<!--SM:$listitem@key:SM-->" name="<!--SM:$listitem@key:SM-->" value="<!--SM:if isset($listitem.default_value):SM--><!--SM:$listitem.default_value:SM--><!--SM:/if:SM-->" />
                        <!--SM:/if:SM-->
                        </label>
                    </div>
                <!-- Item 1 - directly addressed items (non-editable) -->
                <!--SM:elseif !is_array($element) && isset($object.labels[$element@key]):SM-->
                    <div id="NE_<!--SM:$object@key:SM-->_<!--SM:$element@key:SM-->">DA: 
                        <label>
                            <!--SM:$object.labels[$element@key]:SM-->
                            <data><!--SM:$element:SM--></data>
                        </label>
                    </div>
                <!--SM:elseif is_array($element):SM-->
                    <!--SM:foreach $element as $portion:SM-->
                <!-- Item 2 - pulled items from other tables - arrays with labels (non-editable) -->
                        <!--SM:if !is_array($portion) && isset($element.labels[$portion@key]):SM-->
                        <div id="NE_<!--SM:$object@key:SM-->_<!--SM:$element@key:SM-->_<!--SM:$portion@key:SM-->">AA:
                            <label>
                                <!--SM:$element.labels[$portion@key]:SM-->
                                <data><!--SM:$portion:SM--></data>
                            </label>
                        </div>
                <!-- Item 3 - directly stored items - arrays in arrays (editable) TODO! -->
                        <!--SM:elseif is_array($portion) && isset($object.isEditable[$element@key]):SM-->
                            <div id="ED_<!--SM:$object@key:SM-->_<!--SM:$element@key:SM-->_<!--SM:$portion@key:SM-->">AAE:
                                Editable portion in here
                            </div>
                <!-- Item 3 - directly stored items - arrays in arrays (non-editable) TODO! -->
                        <!--SM:elseif is_array($portion) && isset($portion.labels[$portion@key]):SM-->
                            <!--SM:foreach $portion as $item:SM-->
                            <div id="NE_<!--SM:$object@key:SM-->_<!--SM:$element@key:SM-->_<!--SM:$portion@key:SM-->_<!--SM:$item@key:SM-->">AAA:
                                <label>
                                    <!--SM:$portion.labels[$item@key]:SM-->
                                    <data><!--SM:$item:SM--></data>
                                </label>
                            </div>
                            <!--SM:/foreach:SM-->
                        <!--SM:/if:SM-->
                    <!--SM:/foreach:SM-->
                <!--SM:/if:SM-->
            <!--SM:/foreach:SM-->
        </div>
    <!--SM:if 0 + substr($SiteConfig.thisurl, -1) > 0 && isset($object.isEditable) && count($object.isEditable) > 0:SM-->
        </form>
    <!--SM:/if:SM-->
    <pre>
<!--SM:var_dump($object):SM-->
    </pre>
<!--SM:/foreach:SM-->