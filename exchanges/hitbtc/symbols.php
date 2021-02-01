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

            $uriSymbols="https://api.hitbtc.com/api/2/public/symbol";

            $arraySymbols=$this->koin->requestJSON($uriSymbols);

            $arrayReturn = array();

            if (!empty($arraySymbols)) {

                $y=0;

                foreach($arraySymbols as $k => $v) {

                    if ($type == $v["quoteCurrency"]) {

                        $arrayReturn[$y]["currency"] = $v["baseCurrency"];
                        $arrayReturn[$y]["symbol"] = $v["id"];
                        $arrayReturn[$y]["tickSize"] = tickSize($v["tickSize"]);
                        $arrayReturn[$y]["type"] = $v["quoteCurrency"];
                        $arrayReturn[$y]["quantityIncrement"] = $v["quantityIncrement"];

                        $y++;

                    }

                }

            }

            return $arrayReturn;

        }

    }

?>