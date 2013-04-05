<tmpl:menu_wrapper>
    <ul id="igz_menu_item_{$parent_id}_{$menu_iterator}" {$display} >
        {$menu_items}
    </ul>
</tmpl:menu_wrapper>

<tmpl:menu_item>
    <li><a href="{$url}" {$onclick}>{$title}</a></li>
</tmpl:menu_item>

<tmpl:menu_onclick>
    onclick="$('#igz_menu_item_{$id}_{$menu_iterator}').toggle();"
</tmpl:menu_onclick>