<?php
/**
* 2017 Manuel José Pulgar Anguita
*
*  @author    Manuel José Pulgar Anguita for Affilired SL
*  @copyright Affilired
*  @version   0.8
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  http://affilired.com
*  http://spindok.com
*/

if (!defined( '_PS_VERSION_' ))
	exit;



class Affilired extends Module
{
	public function __construct()
	{
		$this->name = 'affilired';
		$this->description = 'We config for you our tracking system in your store!';
		$this->tab = 'front_office_features';
		$this->version = '0.8';
		$this->author = 'Manuel José Pulgar Anguita';
		$this->need_instance = 0;

		$this->ps_versions_compliancy = array('min' => '1.5');

		$this->bootstrap = true;

		parent::__construct();

		$this->displayName = $this->l('Affilired tracking code for Prestashop');
		$this->description = $this->l('Place the Affilired tracking code into your store easily!');

		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

		//store selection
		$this->selected_store_id = (!$this->emptyTest( Tools::getValue('affilired_shop_select') ) )
								? (int)Tools::getValue('affilired_shop_select')
								: $this->context->shop->id;

	}

	public function install()
	{
		if (Shop::isFeatureActive())
			Shop::setContext(Shop::CONTEXT_ALL);

		if (!parent::install() || !$this->registerHook('footer') 
								|| !$this->registerHook('displayOrderConfirmation') 
								|| !AffiliredModel::createTables())
			return false;

		$this->_clearCache('masterTag.tpl');
		$this->_clearCache('confirmation.tpl');

		return true;
	}

	public function uninstall()
	{
		$this->_clearCache('masterTag.tpl');
		$this->_clearCache('confirmation.tpl');

		if (!parent::uninstall()
			|| !AffiliredModel::DropTables())
		{
			return false;
		}
		return true;
	}

	/** Generic hook call funtion */
	public function __call($method, $args)
	{
		//if method exists
		if (function_exists($method))
			return call_user_func_array($method, $args);

		//check for a call to an hook
		if (strpos($method, 'hook') !== false)
			return $this->genericHookMethod( $args[0] );

	}

	public function genericHookMethod()
	{
		$merchant_querry = AffiliredModel::getContent( $this->selected_store_id );

		$merchant_id = $merchant_querry['merchant_id'];

		if (empty($merchant_id)) {
			return false;
		}
		else {

			$current_page = $this -> context -> controller -> php_self;

			if ($current_page == 'order-confirmation') { // the confirmation script is being launched
				// we launch it in hookOrderConfirmation
			}
			else { // the master tag is launched
				$this->context->smarty->assign(
					array( 'merchant_id' =>  $merchant_id)
				);
				return $this->display(__FILE__, 'views/templates/front/masterTag.tpl');
			}
		}
	}

	public function hookDisplayOrderConfirmation($params) {
		
		$merchant_querry = AffiliredModel::getContent( $this->selected_store_id );

		$merchant_id = $merchant_querry['merchant_id'];

		if (empty($merchant_id)) {
			return false;
		}
		else {

			// $order = $params['objOrder'];
			$order = $params['order']; // ps 1.7 compliant
			if (empty($order)) { 
				$order = $params['objOrder']; // ps 1.6 and older compliant
			}

			// array with products with price, quantity (with taxes and without)
			$products = $order->getProducts();

			// die("<pre>".var_export($order,true));

			$theme_string = "";

			if (!empty($products)){
				// foreach ($products as $product){
				$product_indices = array_keys($products);
				for ($index = 0; $index < count($products); $index++){
					$original_index = $product_indices[$index];
					$product = $products[$original_index];
					$product_ordering = $index + 1;
					$this->context->smarty->assign(
						array( 'merchant_id' =>  $merchant_id,
							   'product' => $product,
							   'order' => $order,
							   'product_ordering' => $product_ordering)
					);
					$theme_string .= $this->display(__FILE__, 'views/templates/front/confirmation.tpl');
				}
			}

			return $theme_string;
		}
	}

	public function getContent()
	{
		$this->processSubmit();
		return $this->displayForm();
	}

	public function processSubmit()
	{
		if (Tools::isSubmit('submit'.$this->name))
		{
			$ps_shop_list = $this->getShopsList();
			foreach ($ps_shop_list as $shop) {
				$current_merchant_id = Tools::getValue('merchant_id_'.$shop['id_shop']);
				if (empty($current_merchant_id)) {
					$affilired_merchant_collection = new PrestashopCollection('AffiliredModel');
					$affilired_merchant_collection -> where ('id_store' , '=', "'".$shop['id_shop']."'");
					$hydrated_collection = $affilired_merchant_collection -> getAll();
					foreach ($hydrated_collection as $affilired_merchant) {
						$aaffilired_merchant -> delete();
					}
				}
				else {
					AffiliredModel::setContent( $current_merchant_id, $shop['id_shop'] );
				}
			}

		}
	}

	public function displayForm()
	{

		$ps_shop_list = $this->getShopsList();
		$shops_list = array_merge (array( 0 => array ( 'id_shop' => 0, 'name' => $this -> l('All') )), $ps_shop_list);


		if (!empty($ps_shop_list)){
			$hidden_input_shop = array();
			foreach ($ps_shop_list as $shop){
				$hidden_input_shop[] = array (
						'type' => 'hidden',
						'name' => 'merchant_id_'.$shop['id_shop'],
						'class' => 'hidden_merchant_id'
				);
			}
		}

		$fields_form = array();


		$fields_form[]['form'] = array(
			'input' => $hidden_input_shop);


		$fields_form[]['form'] = array(
				'input' => array(
					array(
						'name' => 'shopList',
						'type' => 'topform',
						'shops' => $shops_list,
						// 'current_shop_id' => $this->selected_store_id,
						'current_shop_id' => 0,
						'multiple' => true,
						'label' => ';)',
						'logoImg' => $this->_path.'img/spindok_logo.png',
						'moduleName' => $this->displayName,
						'moduleDescription' => $this->description,
						'moduleVersion' => $this->version,
						'desc' => $this->l('Please select above the stores .'),

					),
				),
				
			);
		$fields_form[]['form'] = array(
				'tinymce' => true,
				'legend' => array(
					'title' => $this->l('Content Configuration'),
				),
				'input' => array(
					array(
						'type' => 'text',
						'name' => 'merchant_id',
						'label' => $this->l("Your merchant ID in Affilired"),
					),
				),
				'submit' => array(
					'title' => $this->l('Save'),
				),
			);

		$helper = new HelperForm();

		// Module, token and currentIndex
		$helper->module = $this;
		$helper->name_controller = $this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

		// Title and toolbar
		$helper->title = $this->displayName;
		$helper->show_toolbar = true;	 // false -> remove toolbar
		$helper->toolbar_scroll = true;	 // yes - > Toolbar is always visible on the top of the screen.
		$helper->submit_action = 'submit'.$this->name;
		$helper->toolbar_btn = array(
			'save' =>
				array(
					'desc' => $this->l('Save'),
					'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
					'&token='.Tools::getAdminTokenLite('AdminModules'),
				),
			'back' => array(
				'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
				'desc' => $this->l('Back to list')
			)
		);
		// Load current value

		$selectedShop = intval(Tools::getValue('affilired_shop_select'));

		$selected_affilired_obj = AffiliredModel::getContent( $this->selected_store_id );

		$content_query = $selectedShop == 0 ? $this -> getMerchantIDMultiStore() : $selected_affilired_obj;

		$content_field = ( !empty( $content_query ) )? $content_query['merchant_id'] : '';

		$helper->fields_value['merchant_id'] = $content_field;

		foreach ($ps_shop_list as $shop) {

			$current_affilired_obj = AffiliredModel::getContent( $shop['id_shop'] );

			$helper->fields_value['merchant_id_'.$shop['id_shop']] = $current_affilired_obj['merchant_id'];
		}

		if (isset( $this->context ) && isset( $this->context->controller ))
		{
			$this->context->controller->addJs($this->_path.'/js/backoffice.js');
			$this->context->controller->addCss($this->_path.'/css/backoffice.css');

		}
		else
		{
			Tools::addJs($this->_path.'/js/backoffice.js');
			Tools::addCss($this->_path.'/css/backoffice.css');

		}
		return $this->_html.$helper->generateForm($fields_form);
	}

	private function getMerchantIDMultiStore()
	{
		// all the stores are selected
		$affilired_merchant_collection = new PrestashopCollection('AffiliredModel');
		$inflated_collection = $affilired_merchant_collection -> getAll();
		
		$merchant_value = null;

		$shop_list = $this -> getShopsList();
		$number_of_shops = count($shop_list);
		$number_of_affilired_trackings = count($inflated_collection);
		if ($number_of_shops != $number_of_affilired_trackings) {
			$merchant_value = null;
		}
		else {
			
			$firstShop = true;
			$value_changed = false;

			if (!empty($inflated_collection)) {
				foreach ($inflated_collection as $affilired_merchant) {
					if ($firstShop == true) {
						$firstShop = false;
						$merchant_value['merchant_id'] = $affilired_merchant -> merchant_id;
						// die("<pre>".var_export($affilired_merchant, true));
					}
					else {
						if ($value_changed == false) {
							$new_value = $affilired_merchant;
							if ($new_value['merchant_id'] != $merchant_value['merchant_id']) {
								$value_changed = true;
								$merchant_value = null;
								break;
							}
						}
					}
				}
			}
		}
		return $merchant_value;
	}

	private function getShopsList()
	{
		$shops_list = array();
		$shops = Shop::getShops();
		foreach ($shops as $shop)
			$shops_list[] = array( 'id_shop' => $shop['id_shop'], 'name' => $shop['name'] );

		return $shops_list;
	}


	/**
	* methods bellow: Added to comply with the prestashop module validation 
	*/
	private function emptyTest($value_in)
	{
		return empty( $value_in )?true:false;
	}
}


/**
* The model in the same file because of the module generator
*/

class AffiliredModel extends ObjectModel
{
	public $id_store;
	public $merchant_id;

	public static $definition = array(
		'table' => 'affilired',
		'primary' => 'content_id',
		'multishop' => true,
		'multilang' => false,
		'fields' => array(
			'id_store' =>      array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
			'merchant_id' =>      array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true)
		),
	);

	public static function createTables()
	{
		//main table for the files
		return (AffiliredModel::createContentTable());
	}

	public static function dropTables()
	{

		$sql = 'DROP TABLE
			`'._DB_PREFIX_.self::$definition['table'].'`
		';
		$result = Db::getInstance()->execute($sql);
		return $result;
	}

	public static function createContentTable()
	{

		$sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.self::$definition['table'].'`(
			`content_id` int(10) unsigned NOT NULL auto_increment,
			`merchant_id` int(10) NOT NULL,
			`id_store` int(10) unsigned NOT NULL default \'1\',
			PRIMARY KEY (`content_id`), UNIQUE (`id_store`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

		return Db::getInstance()->execute($sql);
	}

	public static function setContent($merchant_id = null, $id_store = 1 /*, $id_lang = null*/)
	{
		//special thanks to MarkOG (http://www.prestashop.com/forums/user/817367-markog/)
		$merchant_id = pSQL( $merchant_id, true );
		$id_store = (int)$id_store;
		$sql = 'INSERT INTO `'._DB_PREFIX_.self::$definition['table'].'` (`merchant_id`,`id_store`)
					VALUES ("'.$merchant_id.'","'.$id_store.'")
					ON DUPLICATE KEY UPDATE `merchant_id` = "'.$merchant_id.'"
				';

		return Db::getInstance()->execute( $sql );
	}

	public static function getContent($shop)
	{
		$sql = 'SELECT * FROM '._DB_PREFIX_.self::$definition['table'].' WHERE `id_store`="'.(int)$shop.'"';
		return Db::getInstance()->getRow($sql);
	}
}