{if $available_coupons}
    <div class="available-coupons">
        <h3>{l s='Dla tego produktu dostępny jest kupon!' mod='productcoupons'}</h3>
        <ul>
            {foreach from=$available_coupons item=coupon}
                <li>
                    <b>{$coupon.discounted_price|number_format:2} {Currency::getDefaultCurrency()->sign}</b>
                    {l s='Cena z kodem:' mod='productcoupons'} {$coupon.code}
                </li>
            {/foreach}
        </ul>
    </div>
{/if}