<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: market.proto

namespace Proto\Market;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>proto.market.KChartRequest</code>
 */
class KChartRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>.proto.market.Stock stock = 1;</code>
     */
    protected $stock = null;
    /**
     * start from 1
     *
     * Generated from protobuf field <code>int32 page = 2;</code>
     */
    protected $page = 0;
    /**
     * default 20
     *
     * Generated from protobuf field <code>int32 size = 3;</code>
     */
    protected $size = 0;
    /**
     * default day
     *
     * Generated from protobuf field <code>string type = 4;</code>
     */
    protected $type = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Proto\Market\Stock $stock
     *     @type int $page
     *           start from 1
     *     @type int $size
     *           default 20
     *     @type string $type
     *           default day
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Market::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>.proto.market.Stock stock = 1;</code>
     * @return \Proto\Market\Stock
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * Generated from protobuf field <code>.proto.market.Stock stock = 1;</code>
     * @param \Proto\Market\Stock $var
     * @return $this
     */
    public function setStock($var)
    {
        GPBUtil::checkMessage($var, \Proto\Market\Stock::class);
        $this->stock = $var;

        return $this;
    }

    /**
     * start from 1
     *
     * Generated from protobuf field <code>int32 page = 2;</code>
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * start from 1
     *
     * Generated from protobuf field <code>int32 page = 2;</code>
     * @param int $var
     * @return $this
     */
    public function setPage($var)
    {
        GPBUtil::checkInt32($var);
        $this->page = $var;

        return $this;
    }

    /**
     * default 20
     *
     * Generated from protobuf field <code>int32 size = 3;</code>
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * default 20
     *
     * Generated from protobuf field <code>int32 size = 3;</code>
     * @param int $var
     * @return $this
     */
    public function setSize($var)
    {
        GPBUtil::checkInt32($var);
        $this->size = $var;

        return $this;
    }

    /**
     * default day
     *
     * Generated from protobuf field <code>string type = 4;</code>
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * default day
     *
     * Generated from protobuf field <code>string type = 4;</code>
     * @param string $var
     * @return $this
     */
    public function setType($var)
    {
        GPBUtil::checkString($var, True);
        $this->type = $var;

        return $this;
    }

}

