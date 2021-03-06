<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace ZenMagick\plugins\formHandler;

use ZenMagick\Base\Plugins\Plugin;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use ZenMagick\Base\Toolbox;
use ZenMagick\Http\Sacs\SacsManager;
use ZenMagick\StoreBundle\Entity\Account\Account;

/**
 * Generic form handler.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class FormHandlerPlugin extends Plugin {

    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();

        // mappings
        $pages = $this->get('pages');
        if (0 < strlen($pages)) {
            $pages = explode(',', $pages);
            $settingsService = $this->container->get('settingsService');
            $ext = $settingsService->get('zenmagick.http.templates.ext', '.php');
            $router = $this->container->get('router');
            $routeCollection = new RouteCollection();
            $controller = 'ZenMagick\plugins\formHandler\controller\FormHandlerController::process';
            foreach ($pages as $page) {
                $routeCollection->add($page, new Route('/'.$page, array('_controller' => $controller), array(), array('view' => 'views/'.$page.$ext, 'view:success' => 'redirect://'.$page)));
            }
            $router->getRouteCollection()->addCollection($routeCollection);


            if (Toolbox::asBoolean($this->get('secure'))) {
                // mark as secure
                foreach ($pages as $page) {
                    $this->container->get('sacsManager')->setMapping($page, Account::ANONYMOUS);
                }
            }

            // XXX: authorization, form id?
        }
    }

}
