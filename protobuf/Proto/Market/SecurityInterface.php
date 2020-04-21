<?php
# Generated by the protocol buffer compiler (spiral/php-grpc). DO NOT EDIT!
# source: market.proto

namespace Proto\Market;

use Spiral\GRPC;

interface SecurityInterface extends GRPC\ServiceInterface
{
    // GRPC specific service name.
    public const NAME = "proto.market.Security";

    /**
    * @param GRPC\ContextInterface $ctx
    * @param Stock $in
    * @return InfoResponse
    *
    * @throws GRPC\Exception\InvokeException
    */
    public function fetchInfo(GRPC\ContextInterface $ctx, Stock $in): InfoResponse;

    /**
    * @param GRPC\ContextInterface $ctx
    * @param Stocks $in
    * @return QuoteResponse
    *
    * @throws GRPC\Exception\InvokeException
    */
    public function fetchRealtimeQuote(GRPC\ContextInterface $ctx, Stocks $in): QuoteResponse;
}
