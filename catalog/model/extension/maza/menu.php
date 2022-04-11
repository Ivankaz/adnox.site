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
}
