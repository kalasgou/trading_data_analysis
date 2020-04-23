<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: market.proto

namespace Proto\Market\InfoResponse;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>proto.market.InfoResponse.Data</code>
 */
class Data extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>map<string, .proto.market.StockInfo> info = 1;</code>
     */
    private $info;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type array|\Google\Protobuf\Internal\MapField $info
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Market::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>map<string, .proto.market.StockInfo> info = 1;</code>
     * @return \Google\Protobuf\Internal\MapField
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * Generated from protobuf field <code>map<string, .proto.market.StockInfo> info = 1;</code>
     * @param array|\Google\Protobuf\Internal\MapField $var
     * @return $this
     */
    public function setInfo($var)
    {
        $arr = GPBUtil::checkMapField($var, \Google\Protobuf\Internal\GPBType::STRING, \Google\Protobuf\Internal\GPBType::MESSAGE, \Proto\Market\StockInfo::class);
        $this->info = $arr;

        return $this;
    }

}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(Data::class, \Proto\Market\InfoResponse_Data::class);

