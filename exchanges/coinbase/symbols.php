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

            $uriSymbols="https://api.gdax.com/products";
            $arraySymbols = $this->koin->requestJSON($uriSymbols);

            $arrayReturn = array();

            if (!empty($arraySymbols)) {

                $y=0;

                $utype = strtoupper($type);

                foreach($arraySymbols as $k => $v) {

                    if ($v["quote_currency"] == $utype) {

                        $arrayReturn[$y]["currency"] = $v["display_name"];
                        $arrayReturn[$y]["symbol"] = $v["id"];
                        $arrayReturn[$y]["tickSize"] = tickSize($v["quote_increment"]);
                        $arrayReturn[$y]["type"] = $utype;

                        $y++;

                    }

                }

            }

            return $arrayReturn;

        }

    }

?>