<?php

    class tickers implements koinsTicker {

        public function __construct($koin) {

            $this->koin = $koin;

        }

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

            $y = 0;

            $arrayTickers = array();
            $arrayReturn = array();

            if (!empty($arraySymbols)) {

                foreach($arraySymbols as $ks => $vs) {

                    $symbol = $vs["symbol"];

                    $pathTicker = "https://api.gdax.com/products/$symbol/ticker";
                    $pathStats = "https://api.gdax.com/products/$symbol/stats";


                    $dataTickers = $this->koin->requestJSON($pathTicker);
                    $dataStats = $this->koin->requestJSON($pathStats);

                    if (!empty($dataTickers) && !empty($dataStats)) {

                        $v = $dataTickers;
                        $vStats = $dataStats;

                        $arrayReturn[$y]["symbol"] = $symbol;
                        $arrayReturn[$y]["ask"] = str_replace(",","",number_format($v["ask"],$vs["tickSize"]));
                        $arrayReturn[$y]["bid"] = str_replace(",","",number_format($v["bid"],$vs["tickSize"]));
                        $arrayReturn[$y]["low"] = str_replace(",","",number_format($vStats["low"],$vs["tickSize"]));
                        $arrayReturn[$y]["high"] = str_replace(",","",number_format($vStats["high"],$vs["tickSize"]));
                        $arrayReturn[$y]["last"] = str_replace(",","",number_format($vStats["open"],$vs["tickSize"]));
                        $arrayReturn[$y]["volume"] = (!empty($vStats["volume"])) ? $vStats["volume"] : 0;

                        $y++;

                    }

                }

            }

            return $arrayReturn;

        }

    }

?>