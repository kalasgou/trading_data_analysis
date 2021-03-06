<?php
namespace App\Services\System\Concrete;

use App\Helpers\Constants\HKEX\OMDC;
use App\Services\System\Contract\TimetableInterface;
use Illuminate\Support\Facades\Redis;

class Timetable implements TimetableInterface
{
    
    public function __construct()
    {
        $this->redis_key = 'HKEX:Trading_Days';
    }
    
    public function getMarketInfo(string $exchange_code, string $market_code) : array
    {
        try {
            $exchange_code = strtoupper($exchange_code);
            $market_code = strtoupper($market_code);
            
            $fields = [
                'trading_session_sub_id', 
                'trading_ses_status', 
                'trading_ses_control_flag', 
                'start_date_time', 
                'end_date_time', 
                'last_update_ts',
                'last_session_ts'
            ];
            $info = Redis::hmget("{$exchange_code}:Market:{$market_code}:Info", $fields);
            $info = array_combine($fields, $info);
            
            return $info;
        } catch (\RedisException $e) {
            
        } catch (\Exception $e) {
            
        }
    }
    
    public function getCalendar(int $timestamp) : array
    {
        try {
            $day = date('j', $timestamp);
            $month = date('n', $timestamp);
            $year = date('Y', $timestamp);
            $this_day_ts = mktime(0, 0, 0, $month, $day, $year);
            
            // Current Trading Day
            $elems = Redis::zrangebyscore($this->redis_key, $this_day_ts, $this_day_ts);
            
            $calendar['is_trade_day'] = false;
            $calendar['cur_trading_day'] = 0;
            if (isset($elems[0])) {
                $calendar['is_trade_day'] = true;
                $detail = json_decode($elems[0], true);
                $calendar['cur_trading_day'] = $detail['ts'];
            }
            
            // Last Trading Day
            $elems = Redis::zrevrangebyscore($this->redis_key, "({$this_day_ts}", 0, ['limit' => ['offset' => 0, 'count' => 1]]);
            
            $calendar['last_trading_day'] = 0;
            if (isset($elems[0])) {
                $detail = json_decode($elems[0], true);
                $calendar['last_trading_day'] = $detail['ts'];
            }
            
            // Last 5 Trading Days
            $elems = Redis::zrevrangebyscore($this->redis_key, "({$this_day_ts}", 0, ['limit' => ['offset' => 0, 'count' => 5]]);
                        
            $calendar['last_five_trading_days'] = ['start' => 0, 'end' => 0];
            if (!empty($elems)) {
                $len = count($elems);
                $start = json_decode($elems[$len - 1], true);
                $end = json_decode($elems[0], true);
                $calendar['last_five_trading_days'] = ['start' => $start['ts'], 'end' => $end['ts']];
            } 
            
            return $calendar;
            
        } catch (\RedisException $e) {
            
        } catch (\Exception $e) {
            
        }
    }
    
    public function getTradinSession(string $exchange_code = 'HKEX', string $market_code = 'MAIN') : array
    {
        $info = $this->getMarketInfo($exchange_code, $market_code);
        $calendar = $this->getCalendar($info['last_update_ts']);
        
        $session['calendar'] = $calendar;
        $session['use_last_trade_day'] = false;
        $session['is_trading_now'] = false;
        $session['is_CAS_now'] = false;               
        
        $status = [
            'code' => 171, 
            'description' => 'holiday'
        ];
        
        if ($calendar['is_trade_day']) {
            if ($info['trading_session_sub_id'] == OMDC::TSSsID_NOT_YET_OPEN && $info['trading_ses_status'] == OMDC::TSS_UNKNOWN) {
                
                $session['use_last_trade_day'] = true;
                
                $status = [
                    'code' => 101,
                    'description' => "not_yet_open"
                ];
                
            } else if (($info['trading_session_sub_id'] == OMDC::TSSsID_PRE_TRADING && $info['trading_ses_status'] == OMDC::TSS_OPEN) 
                    || ($info['trading_session_sub_id'] == OMDC::TSSsID_NO_CANCEL_MODIFY && $info['trading_ses_status'] == OMDC::TSS_OPEN)
                    || ($info['trading_session_sub_id'] == OMDC::TSSsID_OPENING && $info['trading_ses_status'] == OMDC::TSS_OPEN)) {
                
                $session['is_trading_now'] = true;
                
                $status = [
                    'code' => 111,
                    'description' => 'pre_opening_session',
                ];
                
            } else if ($info['trading_session_sub_id'] == OMDC::TSSsID_QUIESCENT && $info['trading_ses_status'] == OMDC::TSS_HALTED) {
                
                $session['is_trading_now'] = true;
                
                $status = [
                    'code' => 112,
                    'description' => 'quiescent',
                ];
                
            } else if ($info['trading_session_sub_id'] == OMDC::TSSsID_CONTINUOUS_TRADING && $info['trading_ses_status'] == OMDC::TSS_OPEN) {
                
                $session['is_trading_now'] = true;
                
                $status = [
                    'code' => 121,
                    'description' => 'trading_session',
                ];
                
            } else if (($info['trading_session_sub_id'] == OMDC::TSSsID_EXCHANGE_INTERVENTION && $info['trading_ses_status'] == OMDC::TSS_HALTED)
                    || ($info['trading_session_sub_id'] == OMDC::TSSsID_CLOSE && $info['trading_ses_status'] == OMDC::TSS_CLOSED)
                    || ($info['trading_session_sub_id'] == OMDC::TSSsID_ORDER_CANCEL && $info['trading_ses_status'] == OMDC::TSS_OPEN)) {
                        
                $status = [
                    'code' => 131,
                    'description' => 'intervened',
                ];
                
            } else if (($info['trading_session_sub_id'] == OMDC::TSSsID_REFERENCE_PRICE && $info['trading_ses_status'] == OMDC::TSS_PRE_CLOSE)
                    || ($info['trading_session_sub_id'] == OMDC::TSSsID_POST_TRADING && $info['trading_ses_status'] == OMDC::TSS_PRE_CLOSE)
                    || ($info['trading_session_sub_id'] == OMDC::TSSsID_NO_CANCELLATION && $info['trading_ses_status'] == OMDC::TSS_PRE_CLOSE)
                    || ($info['trading_session_sub_id'] == OMDC::TSSsID_RANDOM_CLOSE && $info['trading_ses_status'] == OMDC::TSS_PRE_CLOSE)
                    || ($info['trading_session_sub_id'] == OMDC::TSSsID_CLOSING && $info['trading_ses_status'] == OMDC::TSS_PRE_CLOSE)) {
                        
                $session['is_trading_now'] = true;
                $session['is_CAS_now'] = true;
                
                $status = [
                    'code' => 141,
                    'description' => 'closing_auction_session',
                ];
                
            } else if ($info['trading_session_sub_id'] == OMDC::TSSsID_DAY_CLOSE && $info['trading_ses_status'] == OMDC::TSS_DAY_CLOSED) {
                
                $status = [
                    'code' => 151,
                    'description' => 'day_closed',
                ];
                
            }
            
        } else {
            
            $session['use_last_trade_day'] = true;
        }
        
        $session['cur_status_code'] = $status['code'];
        $session['cur_status_desc'] = __("trading.session_desc.{$status['description']}");
        $session['start_time'] = $info['start_date_time'];
        $session['end_time'] = $info['end_date_time'];
        
        return $session;
    }
    
    public function getTradinDaysByRange(string $start_date, string $end_date, string $orderby = 'asc') : array
    {
        $days = Redis::zrangebyscore($this->redis_key, strtotime($start_date), strtotime($end_date));
        
        return $this->sortTradinDays($days, $orderby);
    }
    
    public function getTradinDaysByCount(string $date, int $step = 50, string $direction = 'backwards', string $orderby = 'asc') : array
    {
        $day_ts = strtotime($date);
        
        $idx = Redis::zrank($this->redis_key, json_encode(['ts' => $day_ts]));
        
        if ($direction === 'forwards') {
            $start_idx = $idx;
            $end_idx = min($start_idx + $step, 99999999);
            
        } else {
            
            $end_idx = $idx;
            $start_idx = max(0, $end_idx - $step);
        }
        
        $days = Redis::zrange($this->redis_key, $start_idx, $end_idx);
        
        return $this->sortTradinDays($days, $orderby);
    }
    
    private function sortTradinDays(array $days, string $orderby) : array
    {
        $trading_days = [];
        if ($orderby === 'desc') {
            
            foreach ($days as $day) {
                $one = json_decode($day, true);
                array_unshift($trading_days, $one['ts']);
            }
            
        } else {
            
            foreach ($days as $day) {
                $one = json_decode($day, true);
                $trading_days[] = $one['ts'];
            }
        }
        
        return $trading_days;
    }
}