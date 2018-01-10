<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

namespace Propel\Generator\Model;

/**
 * An abstract model class to represent objects that belongs to a schema like
 * databases, tables, columns, indices, unices, foreign keys...
 *
 * @author Hans Lellelid <hans@xmpl.org> (Propel)
 * @author Hugo Hamon <webmaster@apprendre-php.com> (Propel)
 */
abstract class MappingModel implements MappingModelInterface
{
    /**
     * The list of attributes.
     *
     * @var array
     */
    protected $attributes;

    /**
     * The list of vendor's information.
     *
     * @var array
     */
    protected $vendorInfos;

    /**
     * Constructor.
     *
     */
    public function __construct()
    {
        $this->attributes  = [];
        $this->vendorInfos = [];
    }

    /**
     * Loads a mapping definition from an array.
     *
     * @param array $attributes
     */
    public function loadMapping(array $attributes)
    {
        $this->attributes = array_change_key_case($attributes, CASE_LOWER);
        $this->setupObject();
    }

    /**
     * This method must be implemented by children classes to hydrate and
     * configure the current object with the loaded mapping definition stored in
     * the protected $attributes array.
     */
    abstract protected function setupObject();

    /**
     * Returns all definition attributes.
     *
     * All attribute names (keys) are lowercase.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Returns a particular attribute by a case-insensitive name.
     *
     * If the attribute is not set, then the second default value is
     * returned instead.
     *
     * @param  string $name
     * @param  mixed  $default
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        $name = strtolower($name);
        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }

        return $default;
    }

    /**
     * Converts a value (Boolean, string or numeric) into a Boolean value.
     *
     * This is to support the default value when used with a boolean column.
     *
     * @param  mixed   $value
     * @return boolean
     */
    protected function booleanValue($value)
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (Boolean) $value;
        }

        return in_array(strtolower($value), [ 'true', 't', 'y', 'yes' ], true);
    }

    protected function getDefaultValueForArray($stringValue)
    {
        $stringValue = trim($stringValue);

        $values = [];
        foreach (explode(',', $stringValue) as $v) {
            $values[] = trim($v);
        }

        return $values;
    }

    /**
     * Adds a new VendorInfo instance to this current model object.
     *
     * @param  Vendor|array $vendor
     * @return Vendor
     */
    public function addVendorInfo($vendor)
    {
        if ($vendor instanceof Vendor) {
            $this->vendorInfos[$vendor->getType()] = $vendor;

            return $vendor;
        }

        $vi = new Vendor();
        $vi->loadMapping($vendor);

        return $this->addVendorInfo($vi);
    }

    /**
     * Returns a VendorInfo object by its type.
     *
     * @param  string     $type
     * @return Vendor
     */
    public function getVendorInfoForType($type)
    {
        if (isset($this->vendorInfos[$type])) {
            return $this->vendorInfos[$type];
        }

        return new Vendor($type);
    }

    /**
     * Returns the list of all vendor information.
     *
     * @return VendorInfo[]
     */
    public function getVendorInformation()
    {
        return $this->vendorInfos;
    }
}
