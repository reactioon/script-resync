<?php

    class tickers implements koinsTicker {

        public function __construct($koin) {
            $this->koin = $koin;
        }

        // public function getBySymbol($symbol) {

        //     $pathTicker = "https://api.hitbtc.com/api/2/public/ticker?symbols=$symbol";

        //     $arrayData = file_get_contents($pathTicker);

        //     $arrayTicker = json_decode($arrayData, true);

        //     $arrayReturn = array();
        //     $arrayReturn["last"] = $arrayTicker[0]['last'];
        //     $arrayReturn["low"] = $arrayTicker[0]['low'];
        //     $arrayReturn["high"] = $arrayTicker[0]['high'];
        //     $arrayReturn["base_volume"] = $arrayTicker[0]['volume'];
        //     $arrayReturn["quote_volume"] = $arrayTicker[0]['volumeQuote'];

        //     return $arrayReturn;

        // }

        /**
         * Get all symbols
         * @return [type] [description]
         */
        public function getTickers($arraySymbols, $exchange, $pair) {

            $arrayTickers = array();

            $arrayReturn = array();

            if (!empty($arraySymbols)) {

                $pathTicker = "https://api.hitbtc.com/api/2/public/ticker";
                $arrayTickers = $this->koin->requestJSON($pathTicker);

                $y=0;

                $pairLength = strlen($pair);

                if (!empty($arrayTickers)) {

                    foreach($arrayTickers as $k => $v) {

                        $symbolLength = strlen($v["symbol"]);

                        $pairSelected = substr($v["symbol"], $symbolLength-$pairLength, $symbolLength);

                        if (strtoupper($pairSelected) == strtoupper($pair)) {

                            $arrayReturn[$y]["symbol"] = $v["symbol"];
                            $arrayReturn[$y]["ask"] = $v["ask"];
                            $arrayReturn[$y]["bid"] = $v["bid"];
                            $arrayReturn[$y]["low"] = $v["low"];
                            $arrayReturn[$y]["high"] = $v["high"];
                            $arrayReturn[$y]["last"] = $v["last"];
                            $arrayReturn[$y]["volume"] = $v["volume"];

                            $y++;

                        }

                    }

                }
                // var_dump($arrayReturn[$y-1]["symbol"]);
                // var_dump($pair);
                // var_dump($arrayReturn);
                // exit;

            }

            return $arrayReturn;

        }

    }

?>