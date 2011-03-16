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
 * A single language.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model
 * @Table(name="languages")
 * @Entity
 */
class ZMLanguage extends ZMObject {
    /**
     * @var integer $languageId
     *
     * @Column(name="languages_id", type="integer", nullable=false)
     * @Id
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $languageId;
    /**
     * @var string $name
     *
     * @Column(name="name", type="string", length=32, nullable=false)
     */
    private $name;
    /**
     * @var string $image
     *
     * @Column(name="image", type="string", length=64, nullable=true)
     */
    private $image;
    /**
     * @var string $code
     *
     * @Column(name="code", type="string", length=2, nullable=false)
     */
    private $code;
    /**
     * @var string $directory
     *
     * @Column(name="directory", type="string", length=32, nullable=true)
     */
    private $directory;
    /**
     * @var integer $sortOrder
     *
     * @Column(name="sort_order", type="integer", nullable=true)
     */
    private $sortOrder;

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
     * Get the language id.
     *
     * @return int $id The language id.
     */
    public function getId() { return $this->languageId; }

    /**
     * Get the language name.
     *
     * @return string $name The language name.
     */
    public function getName() { return $this->name; }

    /**
     * Get the language image.
     *
     * @return string $image The language image.
     */
    public function getImage() { return $this->image; }

    /**
     * Get the language code.
     *
     * @return string $code The language code.
     */
    public function getCode() { return $this->code; }

    /**
     * Get the language directory name.
     *
     * @return string $directory The language directory name.
     */
    public function getDirectory() { return $this->directory; }

    /**
     * Get the language sort order.
     *
     * @return integer $sortOrder
     */
    public function getSortOrder() { return $this->sortOrder; }

    /**
     * Set the language id.
     *
     * @param int $id The language id.
     */
    public function setId($id) { $this->languageId = $id; }

    /**
     * Set the language name.
     *
     * @param string $name The language name.
     */
    public function setName($name) { $this->name = $name; }

    /**
     * Set the language image.
     *
     * @param string $image The language image.
     */
    public function setImage($image) { $this->image = $image; }

    /**
     * Set the language code.
     *
     * @param string $code The language code.
     */
    public function setCode($code) { $this->code = $code; }

    /**
     * Set the language directory name.
     *
     * @param string $directory The language directory name.
     */
    public function setDirectory($directory) { $this->directory = $directory; }

    /**
     * Set the language sort order.
     *
     * @param integer $sortOrder
     */
    public function setSortOrder($sortOrder) { $this->sortOrder = $sortOrder; }
}