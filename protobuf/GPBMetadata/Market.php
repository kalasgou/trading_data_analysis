<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: market.proto

namespace GPBMetadata;

class Market
{
    public static $is_initialized = false;

    public static function initOnce() {
        $pool = \Google\Protobuf\Internal\DescriptorPool::getGeneratedPool();

        if (static::$is_initialized == true) {
          return;
        }
        $pool->internalAddGeneratedFile(hex2bin(
            "0aeb110a0c6d61726b65742e70726f746f120c70726f746f2e6d61726b657422320a0553746f636b12150a0d65786368616e67655f636f646518012001280912120a0a73746f636b5f636f646518022001280922380a08437269746572696112100a086b6579776f726473180120012809120c0a0470616765180220012805120c0a0473697a651803200128052291020a0951756f746174696f6e120d0a057072696365180120012809120c0a046f70656e180220012809120d0a05636c6f7365180320012809120f0a076176657261676518042001280912100a086461795f68696768180520012809120f0a076461795f6c6f7718062001280912120a0a6c6173745f636c6f7365180720012809120f0a076368675f73756d18082001280912110a096368675f726174696f180920012809120e0a06766f6c756d65180a2001280912100a087475726e6f766572180b2001280912150a0d6c6173745f74726164655f7473180c2001280912190a1174726164655f7374617475735f636f6465180d2001280912180a1074726164655f7374617475735f737472180e20012809227a0a045469636b120d0a057072696365180120012809120f0a076176657261676518022001280912110a096368675f726174696f180320012809120f0a076368675f73756d180420012809120e0a06766f6c756d6518052001280912100a087475726e6f766572180620012809120c0a0474696d651807200128092294010a064b4368617274120c0a046f70656e180120012809120d0a05636c6f7365180220012809120c0a0468696768180320012809120b0a036c6f77180420012809120f0a076368675f73756d18052001280912110a096368675f726174696f180620012809120e0a06766f6c756d6518072001280912100a087475726e6f766572180820012809120c0a046461746518092001280922230a06537461747573120c0a04636f6465180120012805120b0a036d7367180220012809222d0a0653746f636b7312230a0673746f636b7318012003280b32132e70726f746f2e6d61726b65742e53746f636b22b5010a0953746f636b496e666f12150a0d65786368616e67655f636f646518012001280912130a0b6d61726b65745f636f646518022001280912110a09707264745f7479706518032001280912120a0a73746f636b5f636f646518042001280912120a0a73746f636b5f6e616d65180520012809120c0a044953494e18062001280912100a0863757272656e637918072001280912110a09626f6172645f6c6f74180820012805120e0a0673746174757318092001280522f4010a0d51756f7465526573706f6e736512240a0673746174757318012001280b32142e70726f746f2e6d61726b65742e537461747573122e0a046461746118022001280b32202e70726f746f2e6d61726b65742e51756f7465526573706f6e73652e446174611a8c010a0444617461123c0a0671756f74657318012003280b322c2e70726f746f2e6d61726b65742e51756f7465526573706f6e73652e446174612e51756f746573456e7472791a460a0b51756f746573456e747279120b0a036b657918012001280912260a0576616c756518022001280b32172e70726f746f2e6d61726b65742e51756f746174696f6e3a02380122eb010a0c496e666f526573706f6e736512240a0673746174757318012001280b32142e70726f746f2e6d61726b65742e537461747573122d0a046461746118022001280b321f2e70726f746f2e6d61726b65742e496e666f526573706f6e73652e446174611a85010a044461746112370a04696e666f18012003280b32292e70726f746f2e6d61726b65742e496e666f526573706f6e73652e446174612e496e666f456e7472791a440a09496e666f456e747279120b0a036b657918012001280912260a0576616c756518022001280b32172e70726f746f2e6d61726b65742e53746f636b496e666f3a023801223f0a0b5469636b5265717565737412220a0573746f636b18012001280b32132e70726f746f2e6d61726b65742e53746f636b120c0a0464617465180220012809228e010a0c5469636b526573706f6e736512240a0673746174757318012001280b32142e70726f746f2e6d61726b65742e537461747573122d0a046461746118022001280b321f2e70726f746f2e6d61726b65742e5469636b526573706f6e73652e446174611a290a044461746112210a057469636b7318012003280b32122e70726f746f2e6d61726b65742e5469636b225d0a0d4b43686172745265717565737412220a0573746f636b18012001280b32132e70726f746f2e6d61726b65742e53746f636b120c0a0470616765180220012805120c0a0473697a65180320012805120c0a04747970651804200128092296010a0e4b4368617274526573706f6e736512240a0673746174757318012001280b32142e70726f746f2e6d61726b65742e537461747573122f0a046461746118022001280b32212e70726f746f2e6d61726b65742e4b4368617274526573706f6e73652e446174611a2d0a044461746112250a076b63686172747318012003280b32142e70726f746f2e6d61726b65742e4b436861727432d6010a085365637572697479123e0a0673656172636812162e70726f746f2e6d61726b65742e43726974657269611a1a2e70726f746f2e6d61726b65742e496e666f526573706f6e73652200123f0a096665746368496e666f12142e70726f746f2e6d61726b65742e53746f636b731a1a2e70726f746f2e6d61726b65742e496e666f526573706f6e7365220012490a1266657463685265616c74696d6551756f746512142e70726f746f2e6d61726b65742e53746f636b731a1b2e70726f746f2e6d61726b65742e51756f7465526573706f6e73652200329b010a05436861727412450a0a66657463685469636b7312192e70726f746f2e6d61726b65742e5469636b526571756573741a1a2e70726f746f2e6d61726b65742e5469636b526573706f6e73652200124b0a0c66657463684b436861727473121b2e70726f746f2e6d61726b65742e4b4368617274526571756573741a1c2e70726f746f2e6d61726b65742e4b4368617274526573706f6e73652200620670726f746f33"
        ), true);

        static::$is_initialized = true;
    }
}

