<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Proto\Market;

/**
 */
class ChartClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * 获取股票K线图（暂时先默认日K）
     * @param \Proto\Market\KChartRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function fetchKCharts(\Proto\Market\KChartRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/proto.market.Chart/fetchKCharts',
        $argument,
        ['\Proto\Market\KChartResponse', 'decode'],
        $metadata, $options);
    }

}
