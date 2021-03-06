<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Proto\Market;

/**
 */
class SecurityClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * 搜索股票
     * @param \Proto\Market\Criteria $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function search(\Proto\Market\Criteria $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/proto.market.Security/search',
        $argument,
        ['\Proto\Market\InfoResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * 获取股票信息
     * @param \Proto\Market\Stocks $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function fetchInfo(\Proto\Market\Stocks $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/proto.market.Security/fetchInfo',
        $argument,
        ['\Proto\Market\InfoResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * 获取股票报价（实时）
     * @param \Proto\Market\Stocks $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function fetchRealtimeQuote(\Proto\Market\Stocks $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/proto.market.Security/fetchRealtimeQuote',
        $argument,
        ['\Proto\Market\QuoteResponse', 'decode'],
        $metadata, $options);
    }

}
