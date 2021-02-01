<?php

    class tickers implements koinsTicker {

        public function __construct($koin) {
            $this->koin = $koin;
        }

        // public function getBySymbol($symbol) {

        //     $asset = str_replace("BRL","", $symbol);

        //     $pathTicker = "https://www.mercadobitcoin.net/api/$asset/ticker";
        //     $arrayTicker = json_decode(file_get_contents($pathTicker), true);

        //     $arrayReturn = array();

        //     $arrayReturn["last"] = $arrayTicker["ticker"]["last"];
        //     $arrayReturn["low"] = $arrayTicker["ticker"]["low"];
        //     $arrayReturn["high"] = $arrayTicker["ticker"]["high"];
        //     $arrayReturn["base_volume"] = $arrayTicker["ticker"]["vol"];
        //     $arrayReturn["quote_volume"] = "?";

        //     return $arrayReturn;

        // }

        /**
         * Get all symbols
         * @return [type] [description]
         */
        public function getTickers($arraySymbols, $exchange, $pair) {

            // files
            $dirBase = BASE_PATH."/data/resync";
            $dirBaseData = BASE_PATH."/data/resync/data";

            if (!is_dir($dirBase)) {
                mkdir($dirBase, 0777);
            }

            if (!is_dir($dirBaseData)) {
                mkdir($dirBaseData, 0777);
            }

            $starttime = microtime(true);

            $arrayTickers = array();
            $arrayReturn = array();

            if (!empty($arraySymbols)) {

                foreach($arraySymbols as $k => $v) {

                    $asset = str_replace("BRL","", $v["symbol"]);

                    $pathTicker = "https://www.mercadobitcoin.net/api/$asset/ticker";
                    // $pathOrders = "https://www.mercadobitcoin.net/api/$asset/orderbook";

                    // $arrayOrders = json_decode(file_get_contents($pathOrders), true);
                    // $arrayTicker = json_decode(file_get_contents($pathTicker), true);

                    $arrayTicker = $this->koin->requestJSON($pathTicker);

                    $arrayReturn[$k]["symbol"] = $v["symbol"];
                    $arrayReturn[$k]["ask"] = $arrayTicker["ticker"]["sell"]; //$arrayOrders["asks"][0][0];
                    $arrayReturn[$k]["bid"] = $arrayTicker["ticker"]["buy"]; //$arrayOrders["bids"][0][0];
                    $arrayReturn[$k]["low"] = $arrayTicker["ticker"]["low"];
                    $arrayReturn[$k]["high"] = $arrayTicker["ticker"]["high"];
                    $arrayReturn[$k]["last"] = $arrayTicker["ticker"]["last"];
                    $arrayReturn[$k]["volume"] = $arrayTicker["ticker"]["vol"];

                }

            }

            return $arrayReturn;

        }

    }

?>