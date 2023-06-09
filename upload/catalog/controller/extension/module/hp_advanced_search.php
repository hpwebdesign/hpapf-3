<?php

class ControllerExtensionModuleHpAdvancedSearch extends Controller {
    public function index() {
        $route = $this->request->get['route'] ?? '';

        if ($route == 'product/search') {

            $this->load->language('extension/module/hp_advanced_search');


            $this->load->model('extension/module/hp_advanced_search');

            $filter = [
                'filter_name'         => $this->request->get['search'] ?? '',
                'filter_tag'          => $this->request->get['tag'] ?? '',
                'filter_description'  => $this->request->get['description'] ?? '',
                'filter_category_id'  => $category_id,
                'filter_sub_category' => $this->request->get['sub_category'] ?? '',
            ];

            $data['zones'] = $this->model_extension_module_hp_advanced_search->getZones($filter);

            foreach ($data['zones'] as $key => &$value) {
                $value['type'] = $value['type'] == 'Kabupaten' ? 'Kab.' : $value['type'];

                $value['name'] = $value['type'] . ' ' . $value['name'];
            }

            // 3 Level Category Search
            $data['categories'] = array();

            $categories_1 = $this->model_extension_module_hp_advanced_search->getCategories(0, $filter);

            foreach ($categories_1 as $category_1) {
                $level_2_data = array();

                $categories_2 = $this->model_extension_module_hp_advanced_search->getCategories($category_1['category_id'], $filter);

                foreach ($categories_2 as $category_2) {
                    $level_3_data = array();

                    $categories_3 = $this->model_extension_module_hp_advanced_search->getCategories($category_2['category_id'], $filter);

                    foreach ($categories_3 as $category_3) {
                        $level_3_data[] = array(
                            'category_id' => $category_3['category_id'],
                            'name'        => $category_3['name'],
                        );
                    }

                    $level_2_data[] = array(
                        'category_id' => $category_2['category_id'],
                        'name'        => $category_2['name'],
                        'children'    => $level_3_data
                    );
                }

                $data['categories'][] = array(
                    'category_id' => $category_1['category_id'],
                    'name'        => $category_1['name'],
                    'children'    => $level_2_data
                );
            }


            return $this->load->view('extension/module/hpapf', $data);
        }
    }

    public function searchPage() {

        /*=======Show Themeconfig=======*/
        $this->load->model('extension/soconfig/general');
        $this->load->language('extension/soconfig/soconfig');
        $data['objlang'] = $this->language;
        $data['soconfig'] = $this->soconfig;
        $data['theme_directory'] = $this->config->get('theme_default_directory');
        $data['our_url'] = $this->registry->get('url');
        /*=======url query parameters=======*/
        $data['url_sidebarsticky'] = isset($this->request->get['sidebarsticky']) ? $this->request->get['sidebarsticky'] : '';
        $data['url_cartinfo'] = isset($this->request->get['cartinfo']) ? $this->request->get['cartinfo'] : '';
        $data['url_thumbgallery'] = isset($this->request->get['thumbgallery']) ? $this->request->get['thumbgallery'] : '';
        $data['url_listview'] = isset($this->request->get['listview']) ? $this->request->get['listview'] : '';
        $data['url_asidePosition'] = isset($this->request->get['asidePosition']) ? $this->request->get['asidePosition'] : '';
        $data['url_asideType'] = isset($this->request->get['asideType']) ? $this->request->get['asideType'] : '';
        $data['url_layoutbox'] = isset($this->request->get['layoutbox']) ? $this->request->get['layoutbox'] : '';

        $this->load->language('product/search');

        $this->load->model('catalog/category');

        $this->load->model('catalog/product');
        $this->load->model('catalog/manufacturer');

        $this->load->model('tool/image');
        $data['mobile'] = $this->soconfig;

        if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
            $data['base'] = $this->config->get('config_ssl');
        } else {
            $data['base'] = $this->config->get('config_url');
        }

        $this->load->model('setting/setting');
        $data['cfp_setting'] = $this->model_setting_setting->getSetting('module_so_call_for_price');

        if (!defined('so_call_for_price')) {
            $this->document->addStyle('catalog/view/javascript/so_call_for_price/css/jquery.fancybox.css');
            //$this->document->addScript('catalog/view/javascript/so_call_for_price/js/jquery.fancybox.js');
            $this->document->addStyle('catalog/view/javascript/so_call_for_price/css/style.css');
            $this->document->addScript('catalog/view/javascript/so_call_for_price/js/script.js');
            define('so_call_for_price', 1);
        }


        $this->load->model('extension/module/so_advanced_search');


        if (isset($this->request->get['location'])) {
            $location = $this->request->get['location'];
        } else {
            $location = '';
        }

        if (isset($this->request->get['max_price'])) {
            $max_price = $this->request->get['max_price'];
        } else {
            $max_price = '';
        }



        if (isset($this->request->get['min_price'])) {
            $min_price = $this->request->get['min_price'];
        } else {
            $min_price = '';
        }



        if (isset($this->request->get['location']) || isset($this->request->get['search'])) {
            $search = $this->request->get['search'];
        } else {
            $search = '';
        }

        if (isset($this->request->get['tag'])) {
            $tag = $this->request->get['tag'];
        } elseif (isset($this->request->get['location']) || isset($this->request->get['search'])) {
            $tag = $this->request->get['search'];
        } else {
            $tag = '';
        }

        if (isset($this->request->get['description'])) {
            $description = $this->request->get['description'];
        } else {
            $description = '';
        }

        if (isset($this->request->get['category_id'])) {
            $category_id = $this->request->get['category_id'];
        } else {
            $category_id = 0;
        }

        if (isset($this->request->get['sub_category'])) {
            $sub_category = $this->request->get['sub_category'];
        } else {
            $sub_category = '';
        }


        if (isset($this->request->get['make_id'])) {
            $make_id = $this->request->get['make_id'];
        } else {
            $make_id = '';
        }

        if (isset($this->request->get['model_id'])) {
            $model_id = $this->request->get['model_id'];
        } else {
            $model_id = '';
        }

        if (isset($this->request->get['engine_id'])) {
            $engine_id = $this->request->get['engine_id'];
        } else {
            $engine_id = '';
        }

        if (isset($this->request->get['year_id'])) {
            $year_id = $this->request->get['year_id'];
        } else {
            $year_id = '';
        }

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'p.product_id';
        }


        if (isset($this->request->get['filter_manufacturer_id'])) {
            $filter_manufacturer_id = $this->request->get['filter_manufacturer_id'];
        } else {
            $filter_manufacturer_id = 0;
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'DESC';
        }


        if (!isset($this->request->get['order']) || ($this->request->get['sort'] ?? '') == 'p.sort_order') {
            $order = 'DESC';
        }

        if (!isset($this->request->get['sort']) || ($this->request->get['sort'] ?? '') == 'p.sort_order') {
            $sort = 'p.product_id';
        }


        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        if (isset($this->request->get['limit'])) {
            $limit = (int)$this->request->get['limit'];
        } else {
            $limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
        }

        if (isset($this->request->get['location']) || isset($this->request->get['search'])) {
            $this->document->setTitle($this->language->get('heading_title') .  ' - ' . $this->request->get['search']);
        } elseif (isset($this->request->get['tag'])) {
            $this->document->setTitle($this->language->get('heading_title') .  ' - ' . $this->language->get('heading_tag') . $this->request->get['tag']);
        } else {
            $this->document->setTitle($this->language->get('heading_title'));
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $url = '';

        if (isset($this->request->get['search'])) {
            $url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
        }


        if (isset($this->request->get['location'])) {
            $url .= '&location=' . $this->request->get['location'];
        }

        if (isset($this->request->get['min_price'])) {
            $url .= '&min_price=' . $this->request->get['min_price'];
        }

        if (isset($this->request->get['max_price'])) {
            $url .= '&max_price=' . $this->request->get['max_price'];
        }

        if (isset($this->request->get['tag'])) {
            $url .= '&tag=' . urlencode(html_entity_decode($this->request->get['tag'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['description'])) {
            $url .= '&description=' . $this->request->get['description'];
        }

        if (isset($this->request->get['category_id'])) {
            $url .= '&category_id=' . $this->request->get['category_id'];
        }

        if (isset($this->request->get['sub_category'])) {
            $url .= '&sub_category=' . $this->request->get['sub_category'];
        }


        if (isset($this->request->get['make_id'])) {
            $url .= '&make_id=' . $this->request->get['make_id'];
        }

        if (isset($this->request->get['model_id'])) {
            $url .= '&model_id=' . $this->request->get['model_id'];
        }

        if (isset($this->request->get['engine_id'])) {
            $url .= '&engine_id=' . $this->request->get['engine_id'];
        }

        if (isset($this->request->get['year_id'])) {
            $url .= '&year_id=' . $this->request->get['year_id'];
        }

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }


        if (!isset($this->request->get['order']) || ($this->request->get['sort'] ?? '') == 'p.sort_order') {
            $order = 'DESC';
        }

        if (!isset($this->request->get['sort']) || ($this->request->get['sort'] ?? '') == 'p.sort_order') {
            $sort = 'p.product_id';
        }



        if (isset($this->request->get['page'], $this->request->get['filter_manufacturer_id'])) {
            $url .= '&filter_manufacturer_id=' . $this->request->get['filter_manufacturer_id'];
        }


        if (isset($this->request->get['limit'])) {
            $url .= '&limit=' . $this->request->get['limit'];
        }

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('product/search', $url)
        );

        if (isset($this->request->get['search'])) {
            $data['heading_title'] = $this->language->get('heading_title') .  ' - ' . $this->request->get['search'];
        } else {
            $data['heading_title'] = $this->language->get('heading_title');
        }

        $data['text_compare'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));

        $data['compare'] = $this->url->link('product/compare');

        // 3 Level Category Search
        $data['categories'] = array();

        $categories_1 = $this->model_catalog_category->getCategories(0);

        foreach ($categories_1 as $category_1) {
            $level_2_data = array();

            $categories_2 = $this->model_catalog_category->getCategories($category_1['category_id']);

            foreach ($categories_2 as $category_2) {
                $level_3_data = array();

                $categories_3 = $this->model_catalog_category->getCategories($category_2['category_id']);

                foreach ($categories_3 as $category_3) {
                    $level_3_data[] = array(
                        'category_id' => $category_3['category_id'],
                        'name'        => $category_3['name'],
                    );
                }

                $level_2_data[] = array(
                    'category_id' => $category_2['category_id'],
                    'name'        => $category_2['name'],
                    'children'    => $level_3_data
                );
            }

            $data['categories'][] = array(
                'category_id' => $category_1['category_id'],
                'name'        => $category_1['name'],
                'children'    => $level_2_data
            );
        }


        if (isset($this->request->get['filter_manufacturer_id'])) {
            $filter_manufacturer_id = $this->request->get['filter_manufacturer_id'];
        }

        $data['products'] = array();

        if (isset($this->request->get['min_price']) || isset($this->request->get['max_price']) || isset($this->request->get['location']) || isset($this->request->get['search']) || isset($this->request->get['tag'])) {
            $filter_data = array(

                'filter_location'         => $location,
                'filter_max_price'         => $max_price,
                'filter_min_price'         => $min_price,
                'filter_name'         => $search,
                'filter_tag'          => $tag,
                'filter_description'  => $description,
                'filter_category_id'  => $category_id,

                'filter_manufacturer_id' => $filter_manufacturer_id,

                'filter_sub_category' => $sub_category,

                'filter_make_id'      => $make_id,
                'filter_model_id'     => $model_id,
                'filter_engine_id'    => $engine_id,
                'filter_year_id'      => $year_id,

                'sort'                => $sort,
                'order'               => $order,
                'start'               => ($page - 1) * $limit,
                'limit'               => $limit
            );


            if ($this->config->get('module_so_advanced_search_status')) {
                $product_total = $this->model_extension_module_so_advanced_search->getTotalProducts($filter_data);
            } else {
                $product_total = $this->model_catalog_product->getTotalProducts($filter_data);
            }



            if ($this->config->get('module_so_advanced_search_status')) {
                $results = $this->model_extension_module_so_advanced_search->getProducts($filter_data);
            } else {
                $results = $this->model_catalog_product->getProducts($filter_data);
            }


            foreach ($results as $result) {
                if ($result['image']) {
                    $image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
                } else {
                    $image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
                }

                if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                    $price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                } else {
                    $price = false;
                }

                if (!is_null($result['special']) && (float)$result['special'] >= 0) {
                    $special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                    $tax_price = (float)$result['special'];
                } else {
                    $special = false;
                    $tax_price = (float)$result['price'];
                }

                if ($this->config->get('config_tax')) {
                    $tax = $this->currency->format($tax_price, $this->session->data['currency']);
                } else {
                    $tax = false;
                }

                if ($this->config->get('config_review_status')) {
                    $rating = (int)$result['rating'];
                } else {
                    $rating = false;
                }


                $seller_id = $this->cart->getSellerByProductId($result['product_id']);
                $seller_profile = $this->cart->getSellerProfile($seller_id);
                $product_info = $this->model_catalog_product->getProduct($result['product_id']);


                $data['hpcm_status'] = $this->config->get('module_hp_coupon_management_status');


                $option_data = array();
                $image_data = array();
                if ($this->config->get('module_so_color_swatches_pro_status')) {
                    $this->load->model('extension/module/so_color_swatches_pro');
                    $data['width_product_list'] = (int)$this->config->get('module_so_color_swatches_pro_width_product_list');
                    if ($data['width_product_list'] == 0) {
                        $data['width_product_list'] = 15;
                    }
                    $data['height_product_list'] = (int)$this->config->get('module_so_color_swatches_pro_height_product_list');
                    if ($data['height_product_list'] == 0) {
                        $data['height_product_list'] = 15;
                    }
                    $data['colorswatch_type'] = $this->config->get('module_so_color_swatches_pro_type');
                    $this->document->addStyle('catalog/view/javascript/so_color_swatches_pro/css/style.css');

                    $options = $this->model_extension_module_so_color_swatches_pro->getProductOptions($result['product_id']);
                    foreach ($options as $option) {
                        $product_option_value_data = array();
                        foreach ($option['product_option_value'] as $option_value) {
                            if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
                                $p_image = $this->model_extension_module_so_color_swatches_pro->getProductImages($result['product_id'], $option_value['option_value_id']);
                                if (isset($p_image['image']) && $p_image['image']) {
                                    $pimage = $this->model_tool_image->resize($p_image['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
                                } else {
                                    $pimage = '';
                                }
                                if (isset($p_image['product_image_id']) && $p_image['product_image_id']) {
                                    $product_image_id = $p_image['product_image_id'];
                                } else {
                                    $product_image_id = '';
                                }
                                $product_option_value_data[] = array(
                                    'product_option_value_id' => $option_value['product_option_value_id'],
                                    'option_value_id'         => $option_value['option_value_id'],
                                    'name'                    => $option_value['name'],
                                    'image'                   => $this->model_tool_image->resize($option_value['image'], $data['width_product_list'], $data['height_product_list']),
                                    'price'                   => $price,
                                    'price_prefix'            => $option_value['price_prefix'],
                                    'color_image'             => $pimage,
                                    'product_image_id'        => $product_image_id
                                );
                            }
                        }
                        $option_data[] = array(
                            'product_option_id'    => $option['product_option_id'],
                            'product_option_value' => $product_option_value_data,
                            'option_id'            => $option['option_id'],
                            'name'                 => $option['name'],
                            'type'                 => $option['type'],
                            'value'                => $option['value'],
                            'required'             => $option['required']
                        );
                    }
                }


                /*======Image Galleries=======*/
                $data['image_galleries'] = array();
                $image_galleries = $this->model_catalog_product->getProductImages($result['product_id']);
                foreach ($image_galleries as $image_gallery) {
                    $data['image_galleries'][] = array(
                        'cart' => $this->model_tool_image->resize($image_gallery['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_height')),
                        'thumb' => $this->model_tool_image->resize($image_gallery['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'))
                    );
                }
                $data['first_gallery'] = array(
                    'cart' => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_height')),
                    'thumb' => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'))
                );
                /*======Check New Label=======*/
                if ((float)$result['special']) $discount = '-' . round((($result['price'] - $result['special']) / $result['price']) * 100, 0) . '%';
                else $discount = false;
                $data['reviews'] = sprintf($this->language->get('text_reviews'), (int)$result['reviews']);

                $data['products'][] = array(

                    'option'      => $option_data,
                    'special_end_date' => $this->model_extension_soconfig_general->getDateEnd($result['product_id']),
                    'image_galleries' => $data['image_galleries'],
                    'first_gallery' => $data['first_gallery'],
                    'discount' => $discount,
                    'stock_status' => $result['stock_status'],
                    'reviews' => $data['reviews'],
                    'href_quickview' => htmlspecialchars_decode($this->url->link('extension/soconfig/quickview&product_id=' . $result['product_id'])),
                    'quantity' => $result['quantity'],
                    'hpcm_status'  => $this->config->get('module_hp_coupon_management_status'),


                    'seller'  => $seller_profile,
                    'viewed'  => $product_info ? $product_info['viewed'] : 0,

                    'product_id'  => $result['product_id'],
                    'thumb'       => $image,
                    'name'        => $result['name'],
                    'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
                    'price'       => $price,
                    'special'     => $special,
                    'tax'         => $tax,
                    'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
                    'rating'      => $result['rating'],
                    'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'] . $url)
                );
            }

            $url = '';

            if (isset($this->request->get['search'])) {
                $url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
            }


            if (isset($this->request->get['location'])) {
                $url .= '&location=' . $this->request->get['location'];
            }

            if (isset($this->request->get['min_price'])) {
                $url .= '&min_price=' . $this->request->get['min_price'];
            }

            if (isset($this->request->get['max_price'])) {
                $url .= '&max_price=' . $this->request->get['max_price'];
            }

            if (isset($this->request->get['tag'])) {
                $url .= '&tag=' . urlencode(html_entity_decode($this->request->get['tag'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($this->request->get['description'])) {
                $url .= '&description=' . $this->request->get['description'];
            }

            if (isset($this->request->get['category_id'])) {
                $url .= '&category_id=' . $this->request->get['category_id'];
            }

            if (isset($this->request->get['sub_category'])) {
                $url .= '&sub_category=' . $this->request->get['sub_category'];
            }

            if (isset($this->request->get['limit'])) {
                $url .= '&limit=' . $this->request->get['limit'];
            }

            $data['sorts'] = array();

            $data['sorts'][] = array(
                'text'  => $this->language->get('text_default'),
                'value' => 'p.sort_order-ASC',
                'href'  => $this->url->link('product/search', 'sort=p.sort_order&order=ASC' . $url)
            );

            $data['sorts'][] = array(
                'text'  => $this->language->get('text_name_asc'),
                'value' => 'pd.name-ASC',
                'href'  => $this->url->link('product/search', 'sort=pd.name&order=ASC' . $url)
            );

            $data['sorts'][] = array(
                'text'  => $this->language->get('text_name_desc'),
                'value' => 'pd.name-DESC',
                'href'  => $this->url->link('product/search', 'sort=pd.name&order=DESC' . $url)
            );

            $data['sorts'][] = array(
                'text'  => $this->language->get('text_price_asc'),
                'value' => 'p.price-ASC',
                'href'  => $this->url->link('product/search', 'sort=p.price&order=ASC' . $url)
            );

            $data['sorts'][] = array(
                'text'  => $this->language->get('text_price_desc'),
                'value' => 'p.price-DESC',
                'href'  => $this->url->link('product/search', 'sort=p.price&order=DESC' . $url)
            );

            if ($this->config->get('config_review_status')) {
                $data['sorts'][] = array(
                    'text'  => $this->language->get('text_rating_desc'),
                    'value' => 'rating-DESC',
                    'href'  => $this->url->link('product/search', 'sort=rating&order=DESC' . $url)
                );

                $data['sorts'][] = array(
                    'text'  => $this->language->get('text_rating_asc'),
                    'value' => 'rating-ASC',
                    'href'  => $this->url->link('product/search', 'sort=rating&order=ASC' . $url)
                );
            }

            $data['sorts'][] = array(
                'text'  => $this->language->get('text_model_asc'),
                'value' => 'p.model-ASC',
                'href'  => $this->url->link('product/search', 'sort=p.model&order=ASC' . $url)
            );

            $data['sorts'][] = array(
                'text'  => $this->language->get('text_model_desc'),
                'value' => 'p.model-DESC',
                'href'  => $this->url->link('product/search', 'sort=p.model&order=DESC' . $url)
            );

            $url = '';

            if (isset($this->request->get['location']) || isset($this->request->get['search'])) {
                $url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
            }


            if (isset($this->request->get['location'])) {
                $url .= '&location=' . $this->request->get['location'];
            }

            if (isset($this->request->get['min_price'])) {
                $url .= '&min_price=' . $this->request->get['min_price'];
            }

            if (isset($this->request->get['max_price'])) {
                $url .= '&max_price=' . $this->request->get['max_price'];
            }

            if (isset($this->request->get['tag'])) {
                $url .= '&tag=' . urlencode(html_entity_decode($this->request->get['tag'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($this->request->get['description'])) {
                $url .= '&description=' . $this->request->get['description'];
            }

            if (isset($this->request->get['category_id'])) {
                $url .= '&category_id=' . $this->request->get['category_id'];
            }

            if (isset($this->request->get['sub_category'])) {
                $url .= '&sub_category=' . $this->request->get['sub_category'];
            }


            if (isset($this->request->get['make_id'])) {
                $url .= '&make_id=' . $this->request->get['make_id'];
            }

            if (isset($this->request->get['model_id'])) {
                $url .= '&model_id=' . $this->request->get['model_id'];
            }

            if (isset($this->request->get['engine_id'])) {
                $url .= '&engine_id=' . $this->request->get['engine_id'];
            }

            if (isset($this->request->get['year_id'])) {
                $url .= '&year_id=' . $this->request->get['year_id'];
            }

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }


            if (isset($this->request->get['filter_manufacturer_id'])) {
                $url .= '&filter_manufacturer_id=' . $this->request->get['filter_manufacturer_id'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            $data['limits'] = array();

            $limits = array_unique(array($this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit'), 25, 50, 75, 100));

            sort($limits);

            foreach ($limits as $value) {
                $data['limits'][] = array(
                    'text'  => $value,
                    'value' => $value,
                    'href'  => $this->url->link('product/search', $url . '&limit=' . $value)
                );
            }

            $url = '';

            if (isset($this->request->get['search'])) {
                $url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
            }


            if (isset($this->request->get['location'])) {
                $url .= '&location=' . $this->request->get['location'];
            }

            if (isset($this->request->get['min_price'])) {
                $url .= '&min_price=' . $this->request->get['min_price'];
            }

            if (isset($this->request->get['max_price'])) {
                $url .= '&max_price=' . $this->request->get['max_price'];
            }
            

            if (isset($this->request->get['tag'])) {
                $url .= '&tag=' . urlencode(html_entity_decode($this->request->get['tag'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($this->request->get['description'])) {
                $url .= '&description=' . $this->request->get['description'];
            }

            if (isset($this->request->get['category_id'])) {
                $url .= '&category_id=' . $this->request->get['category_id'];
            }

            if (isset($this->request->get['sub_category'])) {
                $url .= '&sub_category=' . $this->request->get['sub_category'];
            }


            if (isset($this->request->get['make_id'])) {
                $url .= '&make_id=' . $this->request->get['make_id'];
            }

            if (isset($this->request->get['model_id'])) {
                $url .= '&model_id=' . $this->request->get['model_id'];
            }

            if (isset($this->request->get['engine_id'])) {
                $url .= '&engine_id=' . $this->request->get['engine_id'];
            }

            if (isset($this->request->get['year_id'])) {
                $url .= '&year_id=' . $this->request->get['year_id'];
            }

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }


            if (isset($this->request->get['filter_manufacturer_id'])) {
                $url .= '&filter_manufacturer_id=' . $this->request->get['filter_manufacturer_id'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['limit'])) {
                $url .= '&limit=' . $this->request->get['limit'];
            }

            $pagination = new Pagination();
            $pagination->total = $product_total;
            $pagination->page = $page;
            $pagination->limit = $limit;
            $pagination->url = $this->url->link('product/search', $url . '&page={page}');

            $data['pagination'] = $pagination->render();

            $data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($product_total - $limit)) ? $product_total : ((($page - 1) * $limit) + $limit), $product_total, ceil($product_total / $limit));

            if (isset($this->request->get['location']) || isset($this->request->get['search']) && $this->config->get('config_customer_search')) {
                $this->load->model('account/search');

                if ($this->customer->isLogged()) {
                    $customer_id = $this->customer->getId();
                } else {
                    $customer_id = 0;
                }

                if (isset($this->request->server['REMOTE_ADDR'])) {
                    $ip = $this->request->server['REMOTE_ADDR'];
                } else {
                    $ip = '';
                }

                $search_data = array(
                    'keyword'       => $search,
                    'category_id'   => $category_id,
                    'sub_category'  => $sub_category,
                    'description'   => $description,
                    'products'      => $product_total,
                    'customer_id'   => $customer_id,
                    'ip'            => $ip
                );

                $this->model_account_search->addSearch($search_data);
            }
        }

        $data['search'] = $search;
        $data['description'] = $description;
        $data['category_id'] = $category_id;
        $data['sub_category'] = $sub_category;

        $data['sort'] = $sort;
        $data['order'] = $order;
        $data['limit'] = $limit;

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');


        if (isset($this->request->get['ajax'])) {
            $this->response->setOutput($this->load->view('extension/module/hpapf_product_list_ajax', $data));
        } else {
            $this->response->setOutput($this->load->view('extension/module/hpapf_product_list', $data));
        }
    }
}
