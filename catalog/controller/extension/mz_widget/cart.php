<?php
class ControllerExtensionMzWidgetCart extends maza\layout\Widget {
	private  $setting = array();
	
	public function index(array $setting): string {
		$this->load->model('tool/upload');
		$this->load->language('checkout/cart');
		$this->load->language('common/cart');
		
		$this->setting = $setting;
		
		$data = array();
		
		// Get layout setting
		$data = array_merge($data, $this->getLayoutSetting());
		
		// Get cart data
		$data = array_merge($data, $this->getCartData());
		
		$data['cart'] = $this->url->link('checkout/cart');
		$data['checkout'] = $this->url->link('checkout/checkout', '', true);
		
		$data['format_total'] = sprintf(preg_quote($this->language->get('text_items')), '(\d+)', '(.+)');
		
		return $this->load->view('extension/mz_widget/cart', $data);			
	}
	
	/**
	 *  Get cart data
	 */
	private function getCartData(): array {
		$data = array();
		
		// Totals
		$totals = array();
		$taxes = $this->cart->getTaxes();
		$total = 0;

		// Because __call can not keep var references so we put them into an array.
		$total_data = array(
			'totals' => &$totals,
			'taxes'  => &$taxes,
			'total'  => &$total
		);
	
		// Display prices
		if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
			$sort_order = array();

			$results = $this->model_extension_maza_opencart->getExtensions('total');

			foreach ($results as $key => $value) {
				$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
			}

			array_multisort($sort_order, SORT_ASC, $results);

			foreach ($results as $result) {
				if ($this->config->get('total_' . $result['code'] . '_status')) {
					$this->load->model('extension/total/' . $result['code']);

					// We have to put the totals in an array so that they pass by reference.
					$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
				}
			}

			$sort_order = array();

			foreach ($totals as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $totals);
		}
				
		$data['text_items'] = sprintf($this->language->get('text_items'), $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0), $this->currency->format($total, $this->session->data['currency']));

		$data['products'] = array();

		foreach ($this->cart->getProducts() as $product) {
			if ($product['image']) {
				$image = $this->model_tool_image->resize($product['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_height'));
			} else {
				$image = '';
			}

			$option_data = array();

			foreach ($product['option'] as $option) {
				if ($option['type'] != 'file') {
					$value = $option['value'];
				} else {
					$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

					if ($upload_info) {
						$value = $upload_info['name'];
					} else {
						$value = '';
					}
				}

				$option_data[] = array(
					'name'  => $option['name'],
					'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value),
					'type'  => $option['type']
				);
			}

			// Display prices
			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$unit_price = $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'));
				
				$price = $this->currency->format($unit_price, $this->session->data['currency']);
				$total = $this->currency->format($unit_price * $product['quantity'], $this->session->data['currency']);
			} else {
				$price = false;
				$total = false;
			}

			$data['products'][] = array(
				'cart_id'   => $product['cart_id'],
				'thumb'     => $image,
				'name'      => $product['name'],
				'model'     => $product['model'],
				'option'    => $option_data,
				'recurring' => ($product['recurring'] ? $product['recurring']['name'] : ''),
				'quantity'  => $product['quantity'],
				'price'     => $price,
				'total'     => $total,
				'href'      => $this->url->link('product/product', 'product_id=' . $product['product_id'])
			);
		}
		
		// Total items
		$data['total_item'] = $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0);
		
		// Total amount
		$data['total_amount'] = $total;

		// Gift Voucher
		$data['vouchers'] = array();

		if (!empty($this->session->data['vouchers'])) {
			foreach ($this->session->data['vouchers'] as $key => $voucher) {
				$data['vouchers'][] = array(
					'key'         => $key,
					'description' => $voucher['description'],
					'amount'      => $this->currency->format($voucher['amount'], $this->session->data['currency'])
				);
			}
		}

		$data['totals'] = array();

		foreach ($totals as $total) {
			$data['totals'][] = array(
				'title' => $total['title'],
				'text'  => $this->currency->format($total['value'], $this->session->data['currency']),
			);
		}
		
		return $data;
	}
	
	/**
	 * Get layout setting of cart
	 */
	private function getLayoutSetting(): array {
		$data = array();
		
		// layout setting
		$data['title'] = maza\getOfLanguage($this->setting['widget_title']);
		
		$data['icon_width']     = $this->setting['widget_icon_width'];
		$data['icon_height']    = $this->setting['widget_icon_height'];
		$data['icon_size']      = $this->setting['widget_icon_size'];
		$data['icon_font']      = false;
		$data['icon_svg']       = false;
		$data['icon_image']     = false;
		
		$icon_width = $this->setting['widget_icon_width'];
		$icon_height = $this->setting['widget_icon_height'];
		
		// font icon
		$data['icon_font'] = maza\getOfLanguage($this->setting['widget_icon_font']);

		// svg image
		$icon_svg = maza\getOfLanguage($this->setting['widget_icon_svg']);
		if(is_file(MZ_CONFIG::$DIR_SVG_IMAGE . $icon_svg)){
			$data['icon_svg'] = $this->mz_document->addSVG(MZ_CONFIG::$DIR_SVG_IMAGE . $icon_svg);
		}

		// Image
		$icon_image = maza\getOfLanguage($this->setting['widget_icon_image']);
		if(is_file(DIR_IMAGE . $icon_image)){
			list($icon_width, $icon_height) = $this->model_extension_maza_image->getEstimatedSize($icon_image, $icon_width, $icon_height);

			$data['icon_width']     = $icon_width;
			$data['icon_height']    = $icon_height;

			$data['icon_image'] = $this->model_tool_image->resize($icon_image, $icon_width, $icon_height);
		}
		
		return $data;
	}
	
	/**
	 * Change default setting
	 */
	public function getSettings(): array {
		$setting['xl'] = $setting['lg'] = $setting['md'] = 
		$setting['sm'] = $setting['xs'] = array(
			'widget_flex_grow' => 0,
			'widget_flex_shrink' => 0,
		);
		
		return \maza\array_merge_subsequence(parent::getSettings(), $setting);
	}
}
