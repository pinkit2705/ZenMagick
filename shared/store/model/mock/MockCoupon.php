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
namespace zenmagick\apps\store\model\mock;

use zenmagick\apps\store\model\coupons\Coupon;

/**
 * Mock coupon.
 *
 * @author DerManoMann
 */
class MockCoupon extends Coupon {

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct(123, 'abc123');
        $this->setStartDate(time());
        $this->setExpiryDate(time());
        $this->setAmount(20.00);
    }

}
