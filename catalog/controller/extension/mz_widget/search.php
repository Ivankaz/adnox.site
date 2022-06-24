<?php
class ControllerExtensionMzWidgetSearch extends maza\layout\Widget {
	public function index(array $setting): string {
        $this->load->language('extension/mz_widget/search');

        $data = array();

        // placeholder
        $data['placeholder'] = maza\getOfLanguage($setting['widget_placeholder']);

        // Search button type
        $data['search_button_type'] = $setting['widget_search_button_type'];

        // Autocomplete
        if($setting['widget_autocomplete_status']){
            $data['autocomplete'] = $setting['widget_autocomplete_limit'];
        } else {
            $data['autocomplete'] = 0;
        }

        // Search data
        $data['search'] = false;

        if(isset($this->request->get['search'])){
            $data['search'] = $this->request->get['search'];
        }

        if($setting['widget_search_route'] == 'blog' || strpos($this->mz_document->getRoute(), 'extension/maza/blog') === 0){
            $route_target = 'extension/maza/blog/search';
            $data['autocomplete_route'] = 'extension/maza/blog/article/autocomplete';
        } else {
            $route_target = 'product/search';
            $data['autocomplete_route'] = 'extension/maza/product/product/autocomplete';
        }

        // Category
        if($setting['widget_category_status']){
            $data['category_name'] = $data['category_id'] = null;

            $category_info = array();

            if($route_target == 'extension/maza/blog/search'){
                $this->load->model('extension/maza/blog/category');
                $data['categories'] = $this->getBlogCategories(0, $setting['widget_category_depth']);

                if(isset($this->request->get['category_id'])){
                    $category_info = $this->model_extension_maza_blog_category->getCategory($this->request->get['category_id']);
                }
            } else {
                $this->load->model('catalog/category');
                $data['categories'] = $this->getProductCategories(0, $setting['widget_category_depth']);

                if(isset($this->request->get['category_id'])){
                    $category_info = $this->model_catalog_category->getCategory($this->request->get['category_id']);
                }
            }

            if($category_info){
                $data['category_name'] = $category_info['name'];
                $data['category_id'] = $category_info['category_id'];
            }
        }

        $data['action'] = $this->url->link($route_target, true);
        $data['route']  = $route_target;
                
		return $this->load->view('extension/mz_widget/search', $data);
	}

    /**
     * Get product category by level
     * @param Int $parent_id parent id
     * @param Int $depth depth of child category
     */
    private function getProductCategories(int $parent_id, int $depth = 1): array {
        $data = array();

        if($depth){
            $categories = $this->model_catalog_category->getCategories($parent_id);

            foreach ($categories as $category_info) {
                $data[] = array(
                    'category_id' => $category_info['category_id'],
                    'name'        => $category_info['name'],
                    'children'    => $this->getProductCategories($category_info['category_id'], $depth - 1)
                );
            }
        }

        return $data;
    }

    /**
     * Get blog category by level
     * @param Int $parent_id parent id
     * @param Int $depth depth of child category
     */
    private function getBlogCategories(int $parent_id, int $depth = 1): array {
        $data = array();

        if($depth){
            $categories = $this->model_extension_maza_blog_category->getCategories($parent_id);

            foreach ($categories as $category_info) {
                $data[] = array(
                    'category_id' => $category_info['category_id'],
                    'name'        => $category_info['name'],
                    'children'    => $this->getBlogCategories($category_info['category_id'], $depth - 1)
                );
            }
        }

        return $data;
    }
}
