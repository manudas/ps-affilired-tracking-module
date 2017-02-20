{*
*  @author    Manuel José Pulgar Anguita for Affilired
*  @copyright Affilired
*}

{extends file="helpers/form/form.tpl"}


{block name="label"}
    {if $input.type == 'topform'}
        <div id='topform-label' >
             <div class="col-md-3 row" style="background-color: transparent;">
                <div class="top-logo">
                    <img src="{$input.logoImg|escape:html}" alt="contentBox" style="float:left;">
                </div>
                <div class="col-md-8 top-module-description">
                    <h1 class="top-module-title">{$input.moduleName|escape:html}</h1>

                    <div class="top-module-sub-title">{$input.moduleDescription|escape:html}</div>

                    <div class="top-module-my-name"><a href="http://contentbox.org/?v={$input.moduleVersion|escape:html}">Affilired Tracking {$input.moduleVersion|escape:html}</a></div>
                    <div class="">by <a href="http://www.affilired.com">Affilired</a></div>
                </div>
            </div>
           
        </div>        
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{block name="input_row"}
    {if $input.type == 'hidden'}
		<input type="hidden" name="{$input.name}" id="{$input.name}" class="{$input.class}" value="{$fields_value[$input.name]|escape:'html':'UTF-8'}" />				
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{block name="input"}

    {if $input.type == 'topform'}
        <div class="row" style="background-color: transparent;" >
            <div class="col-md-4">
                <span><b>Shop:</b></span>
                <select id="affilired_shop_select" name="affilired_shop_select">
                    {foreach $input.shops as $shop}
                        <option id="id_{$shop['id_shop']|escape}" value="{$shop['id_shop']|escape}"
                            {if ( $input.current_shop_id == $shop['id_shop'] )}
                            selected
                            {/if}
                            >
                            {$shop['name']|escape}
                        </option>
                    {/foreach}
                </select>                
            </div>

            <div class="col-md-3">
                <div >&nbsp;</div>
                <input type="button" value="Select" class="btn btn-primary" id="changeStoreButton">
            </div>
        </div>
    {else}
        {$smarty.block.parent}
    {/if}

{/block}