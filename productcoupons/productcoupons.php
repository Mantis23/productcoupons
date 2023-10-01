<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class ProductCoupons extends Module {
    public function __construct() {
        $this->name = 'productcoupons';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Mateusz Tomczak';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Product Coupons');
        $this->description = $this->l('Displays available coupons for a product.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }

    public function install() {
        return parent::install() && $this->registerHook('displayProductAdditionalInfo') &&  $this->registerHook('displayHeader');
    }

    public function uninstall() {
        return parent::uninstall();
    }

public function fetchAvailableCouponsForProduct($product_id) {
    $cart_rules = CartRule::getCustomerCartRules($this->context->language->id, $this->context->customer->id, true, false);
    $available_coupons = [];

    foreach ($cart_rules as $cart_rule) {
        $cart_rule_obj = new CartRule($cart_rule['id_cart_rule']);
        
        // Pobierz ograniczenia produktów dla reguły koszyka
        $product_restrictions = $cart_rule_obj->getProductRuleGroups();

        $is_applicable = false;
        foreach ($product_restrictions as $group) {
            foreach ($group['product_rules'] as $rule) {
                if ($rule['type'] == 'products' && in_array($product_id, $rule['values'])) {
                    $is_applicable = true;
                    break 2; // Wyjdź z obu pętli foreach
                }
            }
        }

        if ($is_applicable) {
            // Oblicz cenę po obniżce
            $product_price = Product::getPriceStatic($product_id, true, null, 2);
            if ($cart_rule_obj->reduction_percent) {
                $discounted_price = $product_price * (1 - ($cart_rule_obj->reduction_percent / 100));
            } else {
                $discounted_price = $product_price - $cart_rule_obj->reduction_amount;
            }

            $cart_rule['discounted_price'] = $discounted_price;
            $available_coupons[] = $cart_rule;
        }
    }

    return $available_coupons;
}

    public function hookDisplayProductAdditionalInfo($params) {
        $product_id = $params['product']->id;
        $coupons = $this->fetchAvailableCouponsForProduct($product_id);
        $this->context->smarty->assign('available_coupons', $coupons);

        return $this->display(__FILE__, 'views/templates/front/productcoupons.tpl');
    }

  public function hookDisplayHeader() {
    $this->context->controller->registerStylesheet(
        'module-productcoupons-style', // ID dla tego stylu
        'modules/'.$this->name.'/views/css/productcoupons.css', // ścieżka do pliku
        [
            'media' => 'all',
            'priority' => 200 // niższa wartość oznacza wyższy priorytet
        ]
    );
}
}
