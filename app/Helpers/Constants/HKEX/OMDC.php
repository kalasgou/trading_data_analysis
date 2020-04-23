<?php

namespace App\Helpers\Constants\HKEX;

class OMDC
{
    const MARKET_CODE_MAIN = 'MAIN';
    const MARKET_CODE_GEM = "GEM";
    const MARKET_CODE_NASD = 'NASD';
    const MARKET_CODE_ETS = 'ETS';
    
    const TSSsID_NOT_YET_OPEN = 100;
    
    // Pre-opening Session
    const TSSsID_PRE_TRADING = 1;
    const TSSsID_NO_CANCEL_MODIFY = 101;
    const TSSsID_RANDOM_MATCHING = 108;
    const TSSsID_OPENING = 2;
    const TSSsID_QUIESCENT = 7;
    
    // Continuous Trading Session
    const TSSsID_CONTINUOUS_TRADING = 3;
    
    // Closing Auction Sessions
    const TSSsID_REFERENCE_PRICE = 105;
    const TSSsID_POST_TRADING = 5;
    const TSSsID_NO_CANCELLATION = 106;
    const TSSsID_RANDOM_CLOSE = 107;
    const TSSsID_CLOSING = 4;
    
    // Other Sessions
    const TSSsID_EXCHANGE_INTERVENTION = 102;
    const TSSsID_CLOSE = 103;
    const TSSsID_ORDER_CANCEL = 104;
    const TSSsID_DAY_CLOSE = 0;
    
    
    const TSS_UNKNOWN = 0;      // for NO
    const TSS_HALTED = 1;       // for BL, EL
    // const TSS_HALTED = 10;       // for EL
    const TSS_OPEN = 2;         // for [POS] OI, [POS] NC, [POS] MA, CT, OC
    // const TSS_PRE_OPEN = 20;    // for [POS] OI, NW, RM, MA, and BL
    const TSS_CLOSED = 3;       // for CL
    // const TSS_OPEN = 30;         // for CT and OC
    const TSS_PRE_CLOSE = 5;    // for [CAS] RP, [CAS] NW, [CAS] RC, [CAS] MA, [CAS] OI
    // const TSS_PRE_CLOSE = 40;   // for [CAS] RP, OI, NW, RC, MA
    // const TSS_CLOSED = 50;   // for CL
    const TSS_DAY_CLOSED = 100; // for DC
}