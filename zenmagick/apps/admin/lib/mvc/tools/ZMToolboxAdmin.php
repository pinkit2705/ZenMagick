<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
 * Admin related functions.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.tools
 */
class ZMToolboxAdmin extends ZMToolboxTool {

    /**
     * Create a plugin admin page URL.
     *
     * @param string requestId The request id.
     * @param string params Query string style parameter; if <code>''</code>.
     * @param boolean secure Flag to create a secure url; default is <code>true</code>.
     * @return string A full URL.
     */
    public function url($requestId=null, $params='', $secure=true) {
        if (null == $requestId) {
            $requestId = $this->getRequest()->getRequestId();
        }

        //TODO!!!
        $url = $this->getRequest()->url('zmIndex.php', ZM_PAGE_KEY.'='.$requestId.'&'.$params, $secure);

        $url = str_replace('?&amp;', '?', $url);
        $url = str_replace('?&', '?', $url);

        return $url;
    }

    /**
     * Get a view for the given <em>function</em> value.
     *
     * @param ZMRequest request The current request.
     * @param string function The function/controller name.
     * @param boolean forceGet Optional flag to force processing the request as GET
     * rather than what it actually is; default is <code>false</code>.
     * @return ZMView A view or <code>null</code>.
     */
    public function getViewForFkt($request, $function, $forceGet=false) {
        $view = null;
        $controller = null;

        $method = $request->getMethod();
        if ($forceGet) {
            $request->setMethod('GET');
        }

        // try to resolve plugin page controller
        $controllerClass = ZMLoader::makeClassname($function);
        if (ZMLoader::resolve($controllerClass)) {
            $controller = ZMLoader::make($controllerClass);
            $view = $controller->process($request);
        } else if (ZMLoader::resolve($controllerClass.'Controller')) {
            $controller = ZMLoader::make($controllerClass.'Controller');
            $view = $controller->process($request);
        } else if (function_exists($function)) {
            $view = $function(); 
        }

        // revert in any case
        $request->setMethod($method);

        // abuse ZMAdminView as plugin admin view
        if (null != $view && null != $controller && $controller instanceof ZMPluginAdminController) {
            // fix template path
            $view->setTemplatePath(array($controller->getPlugin()->getPluginDirectory().DIRECTORY_SEPARATOR.'content'));
        }

        if (null != $view && $view instanceof ZMView) {
            // taken from ZMController
            // set a few default things...
            $view->setVar('request', $request);
            $view->setVar('session', $request->getSession());
            $toolbox = $request->getToolbox();
            $view->setVar('toolbox', $toolbox);
            // also set individual tools
            $view->setVars($toolbox->getTools());
        }

        if (null != $view && null != $controller) {
            $view->setVars($controller->getViewData($request));
        }

        return $view;
    }

}
