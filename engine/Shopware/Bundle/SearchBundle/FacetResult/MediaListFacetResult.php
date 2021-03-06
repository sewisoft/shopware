<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Bundle\SearchBundle\FacetResult;

use Shopware\Bundle\SearchBundle\FacetResultInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;
use Shopware\Bundle\StoreFrontBundle\Struct\Extendable;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\SearchBundle\FacetResult
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class MediaListFacetResult extends Extendable implements FacetResultInterface
{
    /**
     * @var string
     */
    protected $facetName;

    /**
     * @var bool
     */
    protected $active;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $fieldName;

    /**
     * @var MediaListItem[]
     */
    protected $values;

    /**
     * @var string|null
     */
    protected $template = null;

    /**
     * @param string $facetName
     * @param boolean $active
     * @param string $label
     * @param MediaListItem[] $values
     * @param string $fieldName
     * @param string|null $template
     * @param Attribute[] $attributes
     */
    public function __construct(
        $facetName,
        $active,
        $label,
        $values,
        $fieldName,
        $attributes = [],
        $template = 'frontend/listing/filter/facet-media-list.tpl'
    ) {
        $this->facetName = $facetName;
        $this->active = $active;
        $this->label = $label;
        $this->values = $values;
        $this->fieldName = $fieldName;
        $this->attributes = $attributes;
        $this->template = $template;
    }

    /**
     * @return string
     */
    public function getFacetName()
    {
        return $this->facetName;
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return MediaListItem[]
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @inheritdoc
     */
    public function getTemplate()
    {
        return $this->template;
    }
}
