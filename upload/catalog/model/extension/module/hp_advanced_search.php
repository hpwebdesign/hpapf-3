<?php
class ModelExtensionModuleHpAdvancedSearch extends Model {

    public function getZones($data){

        $sql = "(SELECT zone_id FROM " . DB_PREFIX . "vendor v INNER JOIN " . DB_PREFIX . "vendor_to_product vp ON v.vendor_id = vp.vendor_id WHERE vp.product_id IN(" .$this->getProductsSql($data). "))";

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone WHERE zone_id IN (" . $sql . ")");

        return $query->rows;
    }

    public function getCategories($parent_id, $data){
        $sql = "(SELECT category_id FROM " . DB_PREFIX . "product_to_category WHERE product_id IN(" .$this->getProductsSql($data). "))";

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE c.parent_id = '" . (int)$parent_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'  AND c.status = '1' AND  c.category_id IN (" . $sql . ") ORDER BY c.sort_order, LCASE(cd.name)");

		return $query->rows;

    }


    public function getTotalVendors($data) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor v LEFT JOIN " . DB_PREFIX . "vendor_description vd on(v.vendor_id = vd.vendor_id) WHERE v.vendor_id<>0  AND v.approved!=0   AND v.status!=0  AND vd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		
        if (!empty($data['filter_name'])) {
            $sql .= " AND vd.name LIKE '%" . $data['filter_name'] . "%'";
        }

        $query = $this->db->query($sql);

		return $query->row['total'];
	}


    public function getVendors($data=array()){
		/* 01-02-2019 approved code  23-02-2019 update */ 
		$sql="select * from " . DB_PREFIX . "vendor v LEFT JOIN " . DB_PREFIX . "vendor_description vd on(v.vendor_id = vd.vendor_id) WHERE v.vendor_id<>0  AND v.approved!=0   AND v.status!=0  AND vd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
			
        if (!empty($data['filter_name'])) {
            $sql .= " AND vd.name LIKE '%" . $data['filter_name'] . "%'";
        }
      //  $sql .= " AND "
		$sort_data = array(
			'v.vendor_id'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY v.vendor_id";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);
		return $query->rows;	
 	}


    public function getListPrice($data){
       $max_price =  (float) $this->db->query("SELECT MAX(price) AS max_price FROM " . DB_PREFIX . "product  WHERE product_id IN(" .$this->getProductsSql($data). ")")->row['max_price'] ?? '';


       $min_price = (float) $this->db->query("SELECT MIN(price) AS min_price FROM " . DB_PREFIX . "product  WHERE product_id IN(" .$this->getProductsSql($data). ")")->row['min_price'] ?? 0;

       
       if($max_price == 0){
        return array();
       }

       $list_price = array();

       $list_price[] = $min_price;

       $l = ($max_price - $min_price) / 4;

       $list_price[] = $min_price + $l;

       $list_price[] = $max_price - $l;

       $list_price[] = $max_price;

       $results = array();

       foreach ($list_price as $key => $price) {
        if(!isset($list_price[$key + 1])){
           break;
        }

         $min = $price;

         $max = $list_price[$key + 1];

         $results[] = [
            'text' => $this->currency->format($min, $this->config->get('config_currency')) . ' - ' . $this->currency->format($max, $this->config->get('config_currency')),
            'min' => $min,
            'max' => $max
         ];
       }

       return $results;
    }



    public function getProductsSql($data = array()) {
        $sql = "SELECT p.product_id ";

        if (!empty($data['filter_category_id'])) {
            if (!empty($data['filter_sub_category'])) {
                $sql .= " FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (cp.category_id = p2c.category_id)";
            } else {
                $sql .= " FROM " . DB_PREFIX . "product_to_category p2c";
            }

            if (!empty($data['filter_filter'])) {
                $sql .= " LEFT JOIN " . DB_PREFIX . "product_filter pf ON (p2c.product_id = pf.product_id) LEFT JOIN " . DB_PREFIX . "product p ON (pf.product_id = p.product_id)";
            } else {
                $sql .= " LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id)";
            }
        } else {
            $sql .= " FROM " . DB_PREFIX . "product p";
        }

        $sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

        if (!empty($data['filter_category_id'])) {
            if (!empty($data['filter_sub_category'])) {
                $sql .= " AND cp.path_id = '" . (int)$data['filter_category_id'] . "'";
            } else {
                $sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
            }

            if (!empty($data['filter_filter'])) {
                $implode = array();

                $filters = explode(',', $data['filter_filter']);

                foreach ($filters as $filter_id) {
                    $implode[] = (int)$filter_id;
                }

                $sql .= " AND pf.filter_id IN (" . implode(',', $implode) . ")";
            }
        }

        if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
            $sql .= " AND (";

            if (!empty($data['filter_name'])) {
                $implode = array();

                $words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_name'])));

                foreach ($words as $word) {
                    $implode[] = "pd.name LIKE '%" . $this->db->escape($word) . "%'";
                }

                if ($implode) {
                    $sql .= " " . implode(" AND ", $implode) . "";
                }

                if (!empty($data['filter_description'])) {
                    $sql .= " OR pd.description LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
                }
            }

            if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
                $sql .= " OR ";
            }

            if (!empty($data['filter_tag'])) {
                $implode = array();

                $words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_tag'])));

                foreach ($words as $word) {
                    $implode[] = "pd.tag LIKE '%" . $this->db->escape($word) . "%'";
                }

                if ($implode) {
                    $sql .= " " . implode(" AND ", $implode) . "";
                }
            }

            if (!empty($data['filter_name'])) {
                $sql .= " OR LCASE(p.model) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                $sql .= " OR LCASE(p.sku) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                $sql .= " OR LCASE(p.upc) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                $sql .= " OR LCASE(p.ean) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                $sql .= " OR LCASE(p.jan) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                $sql .= " OR LCASE(p.isbn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                $sql .= " OR LCASE(p.mpn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
            }

            $sql .= ")";
        }

        if (!empty($data['filter_manufacturer_id'])) {
            $sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
        }

        $sql .= " GROUP BY p.product_id";


        $sql .= " ORDER BY p.sort_order";



        $sql .= " ASC, LCASE(pd.name) ASC";


      //  $query = $this->db->query($sql);

        return $sql;
    }
}
