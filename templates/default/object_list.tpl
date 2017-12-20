{extends file="page.tpl"}

{block name="content"}
    {assign var='name' value=$obj->name}
    {assign var='pk' value=$obj->pk}
    {assign var='field' value=$obj->field}
    {assign var='list' value=$obj->getList()}
        <form action="{path_for name="batch-propertieslist" data=["property" => $obj->getRouteName()]}" method="post" id="listform">
        <table class="listing">
            <thead>
                <tr>
                    <th class="actions_row"></th>
                    <th>{$field_name}</th>
                    <th class="actions_row">{_T string="Actions"}</th>
                </tr>
            </thead>
            <tbody>
    {foreach from=$list item=o name=obj_list}
        {assign var="edit_link" value={path_for name="propertyEdit" data=["property" => $obj->getRouteName(), "action" => {_T string="edit" domain="routes"}, "id" => $o->$pk]}}
                <tr class="{if $smarty.foreach.obj_list.iteration % 2 eq 0}even{else}odd{/if}">
                    <td>
                        <input type="checkbox" name="_sel[]" value="{$o->$pk}"/>
                    </td>
                    <td><a href="{if isset($show) and $show eq true}{path_for name="propertyShow" data=["property" => $obj->getRouteName(), "id" => $o->$pk]}{else}{$edit_link}{/if}">{$o->$field}</a></td>
                    <td class="center nowrap">
                        <a href="{$edit_link}"><img src="{base_url}/{$template_subdir}images/icon-edit.png" alt="{_T string="[mod]"}" width="16" height="16"/></a>
                        <a class="delete" href="{path_for name="removeProperty" data=["property" => $obj->getRouteName(), "id" => $o->$pk]}"><img src="{base_url}/{$template_subdir}images/icon-trash.png" alt="{_T string="[del]"}" width="16" height="16" title="{_T string="%property: remove from database" pattern="/%property/" replace=$o->$field domain="auto"}"/></a>
                    </td>
                </tr>
    {foreachelse}
                <tr><td colspan="3" class="emptylist">{_T string="no record found" domain="auto"}</td></tr>
    {/foreach}
            </tbody>
        </table>
    {if $list|@count gt 0}
        <div class="center cright">
            {_T string="Pages:"}<br/>
            <ul class="pages">{$pagination}</ul>
        </div>
    {/if}
            <ul class="selection_menu">
    {if $list|@count gt 0}
                <li>{_T string="Selection:"}</li>
                <li><input type="submit" id="delete" name="delete" value="{_T string="Delete"}"/></li>
    {/if}
                <li>{_T string="Other:" domain="auto"}</li>
                <li><a class="button" id="btnadd" href="{path_for name="propertyEdit" data=["property" => $obj->getRouteName(), "action" => {_T string="add" domain="routes"}]}">{$add_text}</a></li>
            </ul>
        </form>
{/block}

{block name="javascripts"}
    {if $list|@count gt 0}
        <script type="text/javascript">
        var _checkselection = function() {
            var _checkeds = $('table.listing').find('input[type=checkbox]:checked').length;
            if ( _checkeds == 0 ) {
                var _el = $('<div id="pleaseselect" title="{_T string="No entry selected" escape="js" domain="auto"}">{_T string="Please make sure to select at least one entry from the list to perform this action." escape="js" domain="auto"}</div>');
                _el.appendTo('body').dialog({
                    modal: true,
                    buttons: {
                        Ok: function() {
                            $(this).dialog( "close" );
                        }
                    },
                    close: function(event, ui){
                        _el.remove();
                    }
                });
                return false;
            }
            return true;
        }

        {include file="js_removal.tpl"}
        {include file="js_removal.tpl" selector="#delete" deleteurl="'{path_for name="batch-propertieslist" data=["property" => $obj->getRouteName()]}'" extra_check="if (!_checkselection()) {ldelim}return false;{rdelim}" extra_data="delete: true, _sel: $('#listform input[type=\"checkbox\"]:checked').map(function(){ return $(this).val(); }).get()" method="POST"}
        var _is_checked = true;
        var _bind_check = function(){
            $('.checkall').click(function(){
                $('table.listing :checkbox[name="_sel[]"]').each(function(){
                    this.checked = _is_checked;
                });
                _is_checked = !_is_checked;
                return false;
            });
            $('.checkinvert').click(function(){
                $('table.listing :checkbox[name="_sel[]"]').each(function(){
                    this.checked = !$(this).is(':checked');
                });
                return false;
            });
        }
        {* Use of Javascript to draw specific elements that are not relevant is JS is inactive *}
        $(function(){
            var _checklinks = '<div class="checkboxes"><span class="fleft"><a href="#" class="checkall">{_T string="(Un)Check all"}</a> | <a href="#" class="checkinvert">{_T string="Invert selection"}</a></span></div>';
            $('.listing').before(_checklinks);
            $('.listing').after(_checklinks);
            _bind_check();
        });
        </script>
    {/if}
{/block}
