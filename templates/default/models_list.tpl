{extends file="page.tpl"}

{block name="content"}
        <form action="{path_for name="batch-modelslist"}" method="post" id="listform">
        <table class="listing">
            <thead>
                <tr>
                    <th class="actions_row"></th>
                    <th>{_T string="Model" domain="auto"}</th>
                    <th>{_T string="Brand" domain="auto"}</th>
                    <th class="actions_row">{_T string="Actions"}</th>
                </tr>
            </thead>
            <tbody>
    {foreach from=$models item=m name=models_list}
        {assign var='edit_link' value={path_for name="modelEdit" data=["action" => {_T string="edit" domain="routes"}, "id" => $m->id]}}
                <tr class="{if $smarty.foreach.models_list.iteration % 2 eq 0}even{else}odd{/if}">
                    <td>
                        <input type="checkbox" name="_sel[]" value="{$m->id}"/>
                    </td>
                    <td><a href="{$edit_link}">{$m->model}</a></td>
                    <td><a href="{$edit_link}">{$m->obrand->value}</a></td>
                    <td class="center nowrap">
                        <a href="{$edit_link}"><img src="{base_url}/{$template_subdir}images/icon-edit.png" alt="{_T string="[mod]"}" width="16" height="16"/></a>
                        <a href="{path_for name="removeModel" data=["id" => $m->id]}" class="delete"><img src="{base_url}/{$template_subdir}images/icon-trash.png" alt="{_T string="[del]"}" width="16" height="16"/></a>
                    </td>
                </tr>
    {foreachelse}
                <tr><td colspan="4" class="emptylist">{_T string="no record found" domain="auto"}</td></tr>
    {/foreach}
            </tbody>
        </table>
    {if $models|@count gt 0}
        <div class="center cright">
            {_T string="Pages:"}<br/>
            <ul class="pages">{$pagination}</ul>
        </div>
    {/if}
        <ul class="selection_menu">
    {if $models|@count gt 0}
            <li>{_T string="Selection:"}</li>
            <li><input type="submit" id="delete" name="delete" value="{_T string="Delete"}"/></li>
    {/if}
            <li>{_T string="Other:" domain="auto"}</li>
            <li><a class="button" href="{path_for name="modelEdit" data=["action" => {_T string="add" domain="routes"}]}" id="btnadd">{_T string="Add new model" domain="auto"}</a></li>
        </ul>
    </form>
{/block}

{block name="javascripts"}
    {if $models|@count gt 0}
        <script type="text/javascript">
        var _checkselection = function() {
            var _checkeds = $('table.listing').find('input[type=checkbox]:checked').length;
            if ( _checkeds == 0 ) {
                var _el = $('<div id="pleaseselect" title="{_T string="No model selected" escape="js" domain="auto"}">{_T string="Please make sure to select at least one model from the list to perform this action." escape="js" domain="auto"}</div>');
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
        {include file="js_removal.tpl" selector="#delete" deleteurl="'{path_for name="batch-modelslist"}'" extra_check="if (!_checkselection()) {ldelim}return false;{rdelim}" extra_data="delete: true, _sel: $('#listform input[type=\"checkbox\"]:checked').map(function(){ return $(this).val(); }).get()" method="POST"}
        var _is_checked = true;
        var _bind_check = function(){
            $('#checkall').click(function(){
                $('table.listing :checkbox[name="_sel[]"]').each(function(){
                    this.checked = _is_checked;
                });
                _is_checked = !_is_checked;
                return false;
            });
            $('#checkinvert').click(function(){
                $('table.listing :checkbox[name="_sel[]"]').each(function(){
                    this.checked = !$(this).is(':checked');
                });
                return false;
            });
        }
        {* Use of Javascript to draw specific elements that are not relevant is JS is inactive *}
        $(function(){
            $('#table_footer').parent().before('<tr><td id="checkboxes" colspan="4"><span class="fleft"><a href="#" id="checkall">{_T string="(Un)Check all"}</a> | <a href="#" id="checkinvert">{_T string="Invert selection"}</a></span></td></tr>');
            _bind_check();
        });
        </script>
    {/if}
{/block}
