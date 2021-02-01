<?php

    class trades implements koinsTrades {

        public function __construct($koin) {
            $this->koin = $koin;
        }

        /**
         * Get all symbols
         * @return [type] [description]
         */
        public function getTrades($arraySymbols, $exchange) {

            // files
            $dirBase = BASE_PATH."/data/resync";
            $dirBaseExchange = BASE_PATH."/data/resync/$exchange";
            $dirBaseIds = BASE_PATH."/data/resync/$exchange/ids";
            $dirBaseData = BASE_PATH."/data/resync/data";

            if (!is_dir($dirBase)) {
                mkdir($dirBase, 0777);
            }

            if (!is_dir($dirBaseExchange)) {
                mkdir($dirBaseExchange, 0777);
            }

            if (!is_dir($dirBaseIds)) {
                mkdir($dirBaseIds, 0777);
            }

            if (!is_dir($dirBaseData)) {
                mkdir($dirBaseData, 0777);
            }

            $today = date("Ymd");

            $arrayIds = array();

            $totalRequests=0;

            $arrayTrades = array();

            foreach($arraySymbols as $k => $v) {

                $symbol = $arraySymbols[$k]["symbol"];
                $dirBaseIdsSymbol = "$dirBaseIds/$symbol";

                if (!is_dir($dirBaseIdsSymbol)) {
                    mkdir($dirBaseIdsSymbol, 0777);
                }
                #echo "oie";
                $lockFile = $dirBaseIdsSymbol . "/lock.json";
                if (!file_exists($lockFile)) {

                    $uriPath = "https://api.hitbtc.com/api/2/public/trades/$symbol?sort=DESC&limit=100";
                    $arrayData = json_decode(@file_get_contents($uriPath), true);

                    $totalRequests++;

                    $totalBuy = 0;
                    $totalSell = 0;

                    if (!empty($arrayData)) {

                        foreach($arrayData as $k2 => $v2) {

                            $timestamp = date("Ymd",strtotime($v2["timestamp"]));

                            // load id
                            if (empty($arrayIds[$timestamp])) {
                                if (file_exists($dirBaseIdsSymbol . "/$timestamp.json")) {
                                    $arrayIds[$timestamp] = json_decode(file_get_contents($dirBaseIdsSymbol . "/$timestamp.json"),true);
                                } else {
                                    $arrayIds[$timestamp] = array();
                                }
                            }

                            if (!in_array($v2["id"], $arrayIds[$timestamp])) {

                                if ($v2["side"] == "buy") {
                                    $totalBuy = ($v2["quantity"]+$totalBuy);
                                } else {
                                    $totalSell = ($v2["quantity"]+$totalSell);
                                }

                                $arrayIds[$timestamp][] = $v2["id"];

                            }

                        }

                        // save ids
                        if (!empty($arrayIds)) {

                            foreach($arrayIds as $kda => $vda) {

                                $fileIds = $dirBaseIdsSymbol . "/$kda.json";
                                file_put_contents($fileIds, json_encode($vda));

                            }

                        }

                        $arrayTrades[$k] = new stdClass;
                        $arrayTrades[$k]->symbol = $symbol;

                        $arrayTrades[$k]->totalSell = new stdClass;
                        $arrayTrades[$k]->totalSell = $totalSell;

                        $arrayTrades[$k]->totalBuy = new stdClass;
                        $arrayTrades[$k]->totalBuy = $totalBuy;

                        $arrayTrades[$k]->timestamp = date("Y-m-d H:i:s", strtotime($arrayData[0]["timestamp"]));

                        // lock file
                        if (file_exists($lockFile) && !empty($arrayTrades[$k]->totalBuy) && !empty($arrayTrades[$k]->totalSell)) {
                            unlink($lockFile);
                        }

                        if (empty($arrayTrades[$k]->totalBuy) || empty($arrayTrades[$k]->totalSell)) {
                            file_put_contents($lockFile, json_encode(array('lock'=>true)));
                        }

                    }

                } else {
                    unlink($lockFile);
                }

            }

            return $arrayTrades;

        }

    }

?>