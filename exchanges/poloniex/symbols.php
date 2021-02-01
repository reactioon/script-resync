<?php

    class symbols implements koinsSymbols {

        public function __construct($koin) {
            $this->koin = $koin;
        }

        /**
         * Get all symbols
         * @return [type] [description]
         */
        public function getAll($type) {

            $uriSymbols="https://poloniex.com/public?command=returnTicker";
            $arraySymbols = $this->koin->requestJSON($uriSymbols);

            $arrayReturn = array();

            if (!empty($arraySymbols)) {

                $y=0;

                foreach($arraySymbols as $k => $v) {

                    $rp = strrpos($k, "BTC_");
                    if ($rp === 0) {

                        $arrayReturn[$y]["currency"] = str_replace("BTC_","",$k);
                        $arrayReturn[$y]["symbol"] = $k;
                        $arrayReturn[$y]["tickSize"] = tickSize($v["last"]);
                        $arrayReturn[$y]["type"] = "BTC";

                        $y++;

                    }

                }

            }

            return $arrayReturn;

        }

    }

?>