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

            $uriSymbols = "https://api.huobipro.com/v1/common/symbols";
            $arraySymbols = $this->koin->requestJSON($uriSymbols);

            $arrayReturn = array();

            if (!empty($arraySymbols)) {

                $y=0;

                foreach($arraySymbols["data"] as $k => $v) {

                    if ($v["quote-currency"] == strtolower($type)) {

                        $arrayReturn[$y]["currency"] = $v["base-currency"];
                        $arrayReturn[$y]["symbol"] = $v["base-currency"] . $v["quote-currency"];
                        $arrayReturn[$y]["tickSize"] = $v["price-precision"];
                        $arrayReturn[$y]["type"] = "BTC";

                        $y++;

                    }

                }

            }

            return $arrayReturn;

        }

    }

?>