{include file="header.tpl" title="Add Entry"}
{if count($Creation_Values)>0}
<form action="{$SiteConfig.thisurl}" method="post">
{foreach $Creation_Values as $value}
    <div id="{$value@key}">
        <label for="{$value@key}">{if isset($value.label)}{$value.label}{else}{$value@key}{/if}{if isset($value.required)} <span class="required">(required)</span>{/if}</label>
{if isset($value.list)}
        <select id="{$value@key}" name="{$value@key}">
{if isset($value.optional)}
            <option value="">N/A</option>
{/if}
{foreach $value.list as $listitem}
            <option value="{$listitem@key}" {if isset($value.default_value) && $value.default_value == $listitem@key} selected="selected"{/if}>{$listitem}</option>
{/foreach}
        </select>
{else}
        <input type="{if isset($value.input_type)}{$value.input_type}{else}text{/if}"  id="{$value@key}" name="{$value@key}" value="{if isset($value.default_value)}{$value.default_value}{/if}" />
{/if}
    </div>
{/foreach}
</form>
{/if}
{include file="footer.tpl"}