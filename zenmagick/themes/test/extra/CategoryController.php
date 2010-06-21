<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */
?>
<?php


/**
 * Request controller for categories.
 *
 * @author DerManoMann
 * @package org.zenmagick.rp.uip.controller
 * @version $Id$
 */
class CategoryController extends ZMController {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $viewName = 'error';
        $method = null;
        $args = null;

        // decide what to do
        if (null != $request->getCategoryPath()) {
            $method = "getProductsForCategoryId";
            $args = array($request->getCategoryId());
            $viewName = 'category_list';
        } else if (null != $request->getManufacturerId()) {
            $method = "getProductsForManufacturerId";
            $args = array($request->getManufacturerId());
            $viewName = 'manufacturer';
        } else if (null != $request->getParameter('compareId')) {
            $method = "getProductsForIds";
            $args = array($request->getParameter('compareId'));
            $viewName = 'category_list';
        }

        // get category
        if (null == ($category = ZMCategories::instance()->getCategoryForId($request->getCategoryId(), $request->getLanguageId())) || !$category->isActive()) {
            return $this->findView('category_not_found');
        }

        $resultList = null;
        $data = array();
        if (null !== $method) {
            $resultSource = ZMLoader::make("ObjectResultSource", 'Product', ZMProducts::instance(), $method, $args);
            $resultList = ZMLoader::make("ResultList");
            $resultList->setResultSource($resultSource);
            $resultList->setPageNumber($request->getPageIndex());
            foreach (explode(',', ZMSettings::get('resultListProductFilter')) as $filter) {
                $resultList->addFilter(ZMLoader::make($filter));
            }
            foreach (explode(',', ZMSettings::get('resultListProductSorter')) as $sorter) {
                $resultList->addSorter(ZMLoader::make($sorter));
            }
            $data['resultList'] = $resultList;
        }

        if ($viewName == "category_list" 
            && ((null == $resultList || !$resultList->hasResults() || (null != $category && $category->hasChildren())) 
                && ZMSettings::get('isUseCategoryPage'))) {
            $viewName = 'category';
        }

        $category = ZMCategories::instance()->getCategoryForId($request->getCategoryId(), $request->getLanguageId());
        if (null != $category) {
            $data['currentCategory'] = $category;
        }

        if (null != $resultList && 1 == $resultList->getNumberOfResults() && ZMSettings::get('isSkipSingleProductCategory')) {
            $results = $resultList->getResults();
            $product = array_pop($results);
            $request->setParameter('products_id', $product->getId());
            $viewName = 'product_info';
        }

        return $this->findView($viewName, $data);
    }

}

?>
