<?php

    class tickers implements koinsTicker {

        public function __construct($koin) {
            $this->koin = $koin;
        }

        public function getTickers($arraySymbols, $exchange, $pair) {

            $y = 0;

            $arrayTickers = array();

            if (!empty($arraySymbols)) {

                foreach($arraySymbols as $ks => $vs) {

                    $symbol = $vs["symbol"];

                    $pathTicker = "https://www.okex.com/api/v1/ticker.do?symbol=$symbol";
                    $arrayTickers = $this->koin->requestJSON($pathTicker);

                    if (!empty($arrayTickers)) {

                        $v = $arrayTickers["ticker"];

                        $arrayReturn[$y]["symbol"] = $symbol;
                        $arrayReturn[$y]["ask"] = $v["sell"];
                        $arrayReturn[$y]["bid"] = $v["buy"];
                        $arrayReturn[$y]["low"] = $v["low"];
                        $arrayReturn[$y]["high"] = $v["high"];
                        $arrayReturn[$y]["last"] = $v["last"];
                        $arrayReturn[$y]["volume"] = $v["vol"];

                        $y++;

                    }

                }

            }

            return $arrayReturn;

        }

    }

?>