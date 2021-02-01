<?php

    class tickers implements koinsTicker {

        public function __construct($koin) {
            $this->koin = $koin;
        }

        // public function getBySymbol($symbol) {

        //     $pathTicker = "https://api.binance.com/api/v1/ticker/24hr?symbol=$symbol";
        //     $fileTicker = @file_get_contents($pathTicker);

        //     $arrayTicker = array();
        //     if (!empty($fileTicker)) {

        //         $arrayRequestTicker = json_decode($fileTicker, true);

        //         $arrayTicker["last"] = $arrayRequestTicker['lastPrice'];
        //         $arrayTicker["low"] = $arrayRequestTicker['lowPrice'];
        //         $arrayTicker["high"] = $arrayRequestTicker['highPrice'];
        //         $arrayTicker["base_volume"] = $arrayRequestTicker['volume'];
        //         $arrayTicker["quote_volume"] = $arrayRequestTicker['quoteVolume'];

        //     }

        //     return $arrayTicker;

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

                $pathTicker = "https://api.binance.com/api/v1/ticker/24hr";
                $arrayTickers = $this->koin->requestJSON($pathTicker);

                $y=0;

                $pairLength = strlen($pair);

                if (!empty($arrayTickers)) {

                    foreach($arrayTickers as $k => $v) {

                        $symbolLength = strlen($v["symbol"]);

                        $pairSelected = substr($v["symbol"], $symbolLength-$pairLength, $symbolLength);

                        if (strtoupper($pairSelected) == strtoupper($pair)) {

                            $arrayReturn[$y]["symbol"] = $v["symbol"];
                            $arrayReturn[$y]["ask"] = $v["askPrice"];
                            $arrayReturn[$y]["bid"] = $v["bidPrice"];
                            $arrayReturn[$y]["low"] = $v["lowPrice"];
                            $arrayReturn[$y]["high"] = $v["highPrice"];
                            $arrayReturn[$y]["last"] = $v["lastPrice"];
                            $arrayReturn[$y]["volume"] = $v["volume"];

                            $y++;

                        }

                    }

                }

            }

            return $arrayReturn;

        }

    }

?>