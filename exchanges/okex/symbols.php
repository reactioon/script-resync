<?php

    class symbols implements koinsSymbols {

        public function __construct($koin) {
            $this->koin = $koin;
        }

        public function getAll($type) {

            $uriSymbols="https://www.okex.com/v2/spot/markets/products";
            $arraySymbols=$this->koin->requestJSON($uriSymbols);

            $arrayReturn = array();

            if (!empty($arraySymbols)) {

                $y=0;

                foreach($arraySymbols["data"] as $k => $v) {

                    $arrayExplode = explode("_", $v["symbol"]);

                    $ltype = strtolower($type);

                    if ($arrayExplode[1] == $ltype && $v["online"] == "1") {

                        $arrayReturn[$y]["currency"] = strtoupper(str_replace("_" . $ltype,"",$v["symbol"]));
                        $arrayReturn[$y]["symbol"] = $v["symbol"];
                        $arrayReturn[$y]["tickSize"] = $v["maxPriceDigit"];
                        $arrayReturn[$y]["type"] = strtoupper($type);

                        $y++;

                    }

                }

            }

            return $arrayReturn;

        }

    }

?>