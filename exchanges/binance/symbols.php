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

            $uriSymbols = "https://api.binance.com/api/v1/exchangeInfo";

            // $arraySymbols = $this->koin->requestJSON($uriSymbols);

            $fileSymbols = file_get_contents($uriSymbols);

            $arraySymbols=array();

            if (!empty($fileSymbols)) {
                $arraySymbols=json_decode($fileSymbols, true);
            }

            $arrayReturn = array();
            // var_dump(count($arraySymbols));
            if (!empty($arraySymbols)) {

                $y=0;

                foreach($arraySymbols["symbols"] as $k => $v) {

                    if ($type == $v["quoteAsset"]) {

                        $arrayReturn[$y]["currency"] = $v["baseAsset"];
                        $arrayReturn[$y]["symbol"] = $v["symbol"];
                        $arrayReturn[$y]["tickSize"] = $v["baseAssetPrecision"];
                        $arrayReturn[$y]["type"] = $v["quoteAsset"];

                        foreach($v["filters"] as $kf => $vf) {

                            if ($vf["filterType"] == "LOT_SIZE") {

                                $quantityIncrement = rtrim($vf["minQty"], "0");

                                if (strlen($quantityIncrement) == 2) {
                                    $quantityIncrement = str_replace(".", "", $quantityIncrement);
                                }

                                $arrayReturn[$y]["quantityIncrement"] = $quantityIncrement;

                            }

                            if ($vf["filterType"] == "MIN_NOTIONAL") {
                                $arrayReturn[$y]["quantityMinimal"] = $vf["minNotional"]*2;
                            }

                        }

                        // $arrayReturn[$y]["quantityMinimal"] = 0.0021;

                        $y++;

                    }

                }

            }

            return $arrayReturn;

        }

    }

?>