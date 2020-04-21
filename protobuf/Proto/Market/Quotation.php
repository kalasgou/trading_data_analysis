<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: market.proto

namespace Proto\Market;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>proto.market.Quotation</code>
 */
class Quotation extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>string price = 1;</code>
     */
    protected $price = '';
    /**
     * Generated from protobuf field <code>string open = 2;</code>
     */
    protected $open = '';
    /**
     * Generated from protobuf field <code>string close = 3;</code>
     */
    protected $close = '';
    /**
     * Generated from protobuf field <code>string average = 4;</code>
     */
    protected $average = '';
    /**
     * Generated from protobuf field <code>string day_high = 5;</code>
     */
    protected $day_high = '';
    /**
     * Generated from protobuf field <code>string day_low = 6;</code>
     */
    protected $day_low = '';
    /**
     * Generated from protobuf field <code>string last_close = 7;</code>
     */
    protected $last_close = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $price
     *     @type string $open
     *     @type string $close
     *     @type string $average
     *     @type string $day_high
     *     @type string $day_low
     *     @type string $last_close
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Market::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>string price = 1;</code>
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Generated from protobuf field <code>string price = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setPrice($var)
    {
        GPBUtil::checkString($var, True);
        $this->price = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string open = 2;</code>
     * @return string
     */
    public function getOpen()
    {
        return $this->open;
    }

    /**
     * Generated from protobuf field <code>string open = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setOpen($var)
    {
        GPBUtil::checkString($var, True);
        $this->open = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string close = 3;</code>
     * @return string
     */
    public function getClose()
    {
        return $this->close;
    }

    /**
     * Generated from protobuf field <code>string close = 3;</code>
     * @param string $var
     * @return $this
     */
    public function setClose($var)
    {
        GPBUtil::checkString($var, True);
        $this->close = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string average = 4;</code>
     * @return string
     */
    public function getAverage()
    {
        return $this->average;
    }

    /**
     * Generated from protobuf field <code>string average = 4;</code>
     * @param string $var
     * @return $this
     */
    public function setAverage($var)
    {
        GPBUtil::checkString($var, True);
        $this->average = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string day_high = 5;</code>
     * @return string
     */
    public function getDayHigh()
    {
        return $this->day_high;
    }

    /**
     * Generated from protobuf field <code>string day_high = 5;</code>
     * @param string $var
     * @return $this
     */
    public function setDayHigh($var)
    {
        GPBUtil::checkString($var, True);
        $this->day_high = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string day_low = 6;</code>
     * @return string
     */
    public function getDayLow()
    {
        return $this->day_low;
    }

    /**
     * Generated from protobuf field <code>string day_low = 6;</code>
     * @param string $var
     * @return $this
     */
    public function setDayLow($var)
    {
        GPBUtil::checkString($var, True);
        $this->day_low = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string last_close = 7;</code>
     * @return string
     */
    public function getLastClose()
    {
        return $this->last_close;
    }

    /**
     * Generated from protobuf field <code>string last_close = 7;</code>
     * @param string $var
     * @return $this
     */
    public function setLastClose($var)
    {
        GPBUtil::checkString($var, True);
        $this->last_close = $var;

        return $this;
    }

}
