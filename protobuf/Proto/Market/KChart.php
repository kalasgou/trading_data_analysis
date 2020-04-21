<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: market.proto

namespace Proto\Market;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>proto.market.KChart</code>
 */
class KChart extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>string open = 1;</code>
     */
    protected $open = '';
    /**
     * Generated from protobuf field <code>string close = 2;</code>
     */
    protected $close = '';
    /**
     * Generated from protobuf field <code>string high = 3;</code>
     */
    protected $high = '';
    /**
     * Generated from protobuf field <code>string low = 4;</code>
     */
    protected $low = '';
    /**
     * Generated from protobuf field <code>string chg_sum = 5;</code>
     */
    protected $chg_sum = '';
    /**
     * Generated from protobuf field <code>string chg_ratio = 6;</code>
     */
    protected $chg_ratio = '';
    /**
     * Generated from protobuf field <code>string volume = 7;</code>
     */
    protected $volume = '';
    /**
     * Generated from protobuf field <code>string turnover = 8;</code>
     */
    protected $turnover = '';
    /**
     * Generated from protobuf field <code>string date = 9;</code>
     */
    protected $date = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $open
     *     @type string $close
     *     @type string $high
     *     @type string $low
     *     @type string $chg_sum
     *     @type string $chg_ratio
     *     @type string $volume
     *     @type string $turnover
     *     @type string $date
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Market::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>string open = 1;</code>
     * @return string
     */
    public function getOpen()
    {
        return $this->open;
    }

    /**
     * Generated from protobuf field <code>string open = 1;</code>
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
     * Generated from protobuf field <code>string close = 2;</code>
     * @return string
     */
    public function getClose()
    {
        return $this->close;
    }

    /**
     * Generated from protobuf field <code>string close = 2;</code>
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
     * Generated from protobuf field <code>string high = 3;</code>
     * @return string
     */
    public function getHigh()
    {
        return $this->high;
    }

    /**
     * Generated from protobuf field <code>string high = 3;</code>
     * @param string $var
     * @return $this
     */
    public function setHigh($var)
    {
        GPBUtil::checkString($var, True);
        $this->high = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string low = 4;</code>
     * @return string
     */
    public function getLow()
    {
        return $this->low;
    }

    /**
     * Generated from protobuf field <code>string low = 4;</code>
     * @param string $var
     * @return $this
     */
    public function setLow($var)
    {
        GPBUtil::checkString($var, True);
        $this->low = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string chg_sum = 5;</code>
     * @return string
     */
    public function getChgSum()
    {
        return $this->chg_sum;
    }

    /**
     * Generated from protobuf field <code>string chg_sum = 5;</code>
     * @param string $var
     * @return $this
     */
    public function setChgSum($var)
    {
        GPBUtil::checkString($var, True);
        $this->chg_sum = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string chg_ratio = 6;</code>
     * @return string
     */
    public function getChgRatio()
    {
        return $this->chg_ratio;
    }

    /**
     * Generated from protobuf field <code>string chg_ratio = 6;</code>
     * @param string $var
     * @return $this
     */
    public function setChgRatio($var)
    {
        GPBUtil::checkString($var, True);
        $this->chg_ratio = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string volume = 7;</code>
     * @return string
     */
    public function getVolume()
    {
        return $this->volume;
    }

    /**
     * Generated from protobuf field <code>string volume = 7;</code>
     * @param string $var
     * @return $this
     */
    public function setVolume($var)
    {
        GPBUtil::checkString($var, True);
        $this->volume = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string turnover = 8;</code>
     * @return string
     */
    public function getTurnover()
    {
        return $this->turnover;
    }

    /**
     * Generated from protobuf field <code>string turnover = 8;</code>
     * @param string $var
     * @return $this
     */
    public function setTurnover($var)
    {
        GPBUtil::checkString($var, True);
        $this->turnover = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string date = 9;</code>
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Generated from protobuf field <code>string date = 9;</code>
     * @param string $var
     * @return $this
     */
    public function setDate($var)
    {
        GPBUtil::checkString($var, True);
        $this->date = $var;

        return $this;
    }

}
