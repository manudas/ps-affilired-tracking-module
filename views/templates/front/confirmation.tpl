{*
*  @author    Manuel José Pulgar Anguita for Affilired
*  @copyright Affilired
*}

{literal}
    <!-- AFFILIRED CONFIRMATION CODE, PLEASE DON'T REMOVE -->
    <script type="text/javascript">
{/literal}   
        // var orderRef = 'SALE_REFERENCE'+' '+'PRODUCT_NAME'; /* You MUST keep the blank space */
        var orderRef = '{$product.id_order}#{$product_ordering}'+' '+'{$product.product_name}'; /* You MUST keep the blank space */
        var payoutCodes = '';
        var offlineCode = '';
        // var uid = 'PRODUCT_UID';
        var uid = '{$product.product_id}';
        var htname = '';
        // var merchantID = 4520;
        var merchantID = {$merchant_id|escape:nofilter};
        var pixel = 0;
        // var orderValue = AMOUNT; /* Commissionable Amount */
        var orderValue = {$product.unit_price_tax_excl|number_format:2:'.':''}; /* Commissionable Amount */
        // var lockingDate = 'LOCKING_DATE'; /* yyyy-mm-dd (separated by hypen) */
        var lockingDate = '{$order -> date_add|date_format:"%Y-%m-%d"}'; /* yyyy-mm-dd (separated by hypen) */
        // var currencyCode ='EUR';
        {if is_array($currency)} {* PS 1.7 compliant: array style *}
            var currencyCode ='{$currency.iso_code}';
        {else} {* PS 1.6 compliant: object style *}
            var currencyCode ='{$currency -> iso_code}';
        {/if}
{literal}        
    </script>
    <!-- <script type="text/javascript" src="//scripts.affilired.com/v2/confirmation.php?merid=4520&uid=PRODUCT_UID"> -->
    <script type="text/javascript" src="//scripts.affilired.com/v2/confirmation.php?merid={/literal}{$merchant_id|escape:nofilter}&uid={$product.product_id}{literal}">
    </script>
    <script type="text/javascript">
        recV3 (orderValue , orderRef, merchantID, uid , htname, pixel, payoutCodes, offlineCode,lockingDate,currencyCode)
    </script>
    <!-- END AFFILIRED CONFIRMATION CODE -->
{/literal}