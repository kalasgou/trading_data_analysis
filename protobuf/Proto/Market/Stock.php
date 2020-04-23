<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: market.proto

namespace Proto\Market;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>proto.market.Stock</code>
 */
class Stock extends \Google\Protobuf\Internal\Message
{
    /**
     * 交易所代码
     *
     * Generated from protobuf field <code>string exchange_code = 1;</code>
     */
    protected $exchange_code = '';
    /**
     * 股票代码
     *
     * Generated from protobuf field <code>string stock_code = 2;</code>
     */
    protected $stock_code = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $exchange_code
     *           交易所代码
     *     @type string $stock_code
     *           股票代码
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Market::initOnce();
        parent::__construct($data);
    }

    /**
     * 交易所代码
     *
     * Generated from protobuf field <code>string exchange_code = 1;</code>
     * @return string
     */
    public function getExchangeCode()
    {
        return $this->exchange_code;
    }

    /**
     * 交易所代码
     *
     * Generated from protobuf field <code>string exchange_code = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setExchangeCode($var)
    {
        GPBUtil::checkString($var, True);
        $this->exchange_code = $var;

        return $this;
    }

    /**
     * 股票代码
     *
     * Generated from protobuf field <code>string stock_code = 2;</code>
     * @return string
     */
    public function getStockCode()
    {
        return $this->stock_code;
    }

    /**
     * 股票代码
     *
     * Generated from protobuf field <code>string stock_code = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setStockCode($var)
    {
        GPBUtil::checkString($var, True);
        $this->stock_code = $var;

        return $this;
    }

}

