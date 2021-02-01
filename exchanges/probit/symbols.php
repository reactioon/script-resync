<?php

    class symbols implements koinsSymbols {

        public function __construct($koin) {
            $this->koin = $koin;
        }

        /**
         * Get all symbols
         * @return [type] [description]
         */
        public function getAll($currency) {

            $uriSymbols="https://api.probit.com/api/exchange/v1/market";

            // $fileSymbols=@file_get_contents($uriSymbols);

            // $arraySymbols=array();
            // if (!empty($fileSymbols)) {
            //     $arraySymbols=json_decode($fileSymbols, true);
            // }

            $arraySymbols = $this->koin->requestJSON($uriSymbols);

            $arrayReturn = array();

            if (!empty($arraySymbols)) {

                $y=0;

                foreach($arraySymbols["data"] as $k => $v) {

                    if ($currency == $v["quote_currency_id"]) {

                        $arrayReturn[$y]["currency"] = $v["base_currency_id"];
                        $arrayReturn[$y]["symbol"] = $v["id"];
                        $arrayReturn[$y]["tickSize"] = tickSize($v["price_increment"]);
                        $arrayReturn[$y]["type"] = $currency;
                        $arrayReturn[$y]["quantityIncrement"] = $v["min_quantity"];
                        $arrayReturn[$y]["quantityMinimal"] = $v["min_cost"]*2;
                        $arrayReturn[$y]["pair"] = $v["quote_currency_id"];

                        $y++;

                    }

                }

            }

            return $arrayReturn;

        }

    }

?>