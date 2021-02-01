<?php

    class tickers implements koinsTicker {

        public function __construct($koin) {
            $this->koin = $koin;
        }

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

            $starttime = microtime(true);

            $arrayTickers = array();
            $arrayReturn = array();

            if (!empty($arraySymbols)) {

                foreach($arraySymbols as $ks => $vs) {

                    $symbol = $vs["symbol"];

                    $pathTicker = "https://api.huobipro.com/market/detail/merged?symbol=$symbol";
                    $dataTickers = $this->koin->requestJSON($pathTicker);

                    if (!empty($dataTickers) && !empty($dataTickers["tick"])) {

                        $v = $dataTickers["tick"];

                        $arrayReturn[$y]["symbol"] = $symbol;
                        $arrayReturn[$y]["ask"] = number_format($v["ask"][0],$vs["tickSize"]);
                        $arrayReturn[$y]["bid"] = number_format($v["bid"][0],$vs["tickSize"]);
                        $arrayReturn[$y]["low"] = number_format($v["low"],$vs["tickSize"]);
                        $arrayReturn[$y]["high"] = number_format($v["high"],$vs["tickSize"]);
                        $arrayReturn[$y]["last"] = number_format($v["close"],$vs["tickSize"]);
                        $arrayReturn[$y]["volume"] = (!empty($v["vol"])) ? $v["vol"] : 0;

                        $y++;

                    }

                }

            }

            return $arrayReturn;

        }

    }

?>