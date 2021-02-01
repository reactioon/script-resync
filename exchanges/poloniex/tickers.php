<?php

    class tickers implements koinsTicker {

        public function __construct($koin) {
            $this->koin = $koin;
        }

        public function getTickers($arraySymbols, $exchange, $pair) {

            $pair = strtoupper($pair);

            $arrayTickers = array();

            if (!empty($arraySymbols)) {

                $pathTicker = "https://poloniex.com/public?command=returnTicker";
                $dataTickers = $this->koin->requestJSON($pathTicker);

                $arrayTickers = $dataTickers;

                $y=0;

                if (!empty($arrayTickers)) {

                    foreach($arrayTickers as $k => $v) {

                        $rp = strrpos($k, "{$pair}_");
                        if ($rp === 0) {

                            $arrayReturn[$y]["symbol"] = $k;
                            $arrayReturn[$y]["ask"] = $v["lowestAsk"];
                            $arrayReturn[$y]["bid"] = $v["highestBid"];
                            $arrayReturn[$y]["low"] = $v["low24hr"];
                            $arrayReturn[$y]["high"] = $v["high24hr"];
                            $arrayReturn[$y]["last"] = $v["last"];
                            $arrayReturn[$y]["volume"] = $v["quoteVolume"];

                            $y++;

                        }

                    }

                }

            }

            return $arrayReturn;

        }

    }

?>