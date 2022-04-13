<?php

class ModelExtensionMazaMenu extends Model
{
    /**
     * Get menu
     * @param Int $menu_id menu id
     * @return Array
     */
    public function getMenu(int $menu_id): array
    {
        $query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "mz_menu` WHERE `status` = '1' AND menu_id = '" . (int)$menu_id . "' LIMIT 1");

        return $query->row;
    }


    /**
     * Get menu items
     * @param Int $menu_id module id
     * @param Int $parent_item_id
     * @return Array items
     */
    public function getItems(int $menu_id, int $parent_item_id = 0): array
    {
        $items_data = array();

        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "mz_menu_item` WHERE `status` = '1' AND customer " . ($this->customer->isLogged() ? '>= 0' : '<= 0') . " AND (customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' OR customer_group_id = 0) AND parent_item_id = '" . (int)$parent_item_id . "' AND menu_id = '" . (int)$menu_id . "' ORDER BY sort_order ASC");

        foreach ($query->rows as $item) {
            $item['setting'] = json_decode($item['setting'], true);

            $items_data[] = $item;
        }

        return $items_data;
    }

    // возвращает вложенные категории
    public function getChildrenCategories($categoryId = null)
    {
        $this->load->model('catalog/category');
        $this->load->model('catalog/product');

        $childrenData = array();

        $children = $this->model_catalog_category->getCategories($categoryId);

        foreach ($children as $child) {
            $filter_data = array(
                'filter_category_id'  => $child['category_id'],
                'filter_sub_category' => true
            );

            $childData = $this->getChildrenCategories($child['category_id']);

            $childrenData[] = array(
                'id' => $child['category_id'],
                'name'  => $child['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
                'children' => $childData,
                'href'  => $this->url->link('product/category', 'path=' . $categoryId . '_' . $child['category_id'])
            );
        }

        // сортирую категории по возрастанию id
        usort($childrenData, function ($a, $b) {
            return ($a['id']-$b['id']);
        });

        return $childrenData;
    }

    // возвращает дерево категорий
    public function getCategoryTree()
    {
        $this->load->model('catalog/category');
        $this->load->model('catalog/product');

        $categoryTree = array();

        $categories = $this->model_catalog_category->getCategories(0);

        foreach ($categories as $category) {
            if ($category['top']) {
                $childrenData = $this->getChildrenCategories($category['category_id']);

                $categoryTree[] = array(
                    'id' => $category['category_id'],
                    'name'     => $category['name'],
                    'children' => $childrenData,
                    'href'     => $this->url->link('product/category', 'path=' . $category['category_id'])
                );
            }
        }

        // сортирую категории по возрастанию id
        usort($categoryTree, function ($a, $b) {
            return ($a['id']-$b['id']);
        });

        return $categoryTree;
    }
}
