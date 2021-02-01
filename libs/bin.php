<?php

    class bin {

        public function load($exchange) {

            // include files
            require_once(PATH_EXCHANGES . "/" . $exchange . "/candles.php");
            require_once(PATH_EXCHANGES . "/" . $exchange . "/symbols.php");
            require_once(PATH_EXCHANGES . "/" . $exchange . "/trades.php");
            require_once(PATH_EXCHANGES . "/" . $exchange . "/tickers.php");

            $this->symbols = new symbols($this);
            $this->candles = new candles($this);
            $this->trades = new trades($this);
            $this->tickers = new tickers($this);

            $this->exchange = $exchange;

        }

        public function requestJSON($url) {

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 3);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

            $data = curl_exec($ch);

            curl_close($ch);

            sleep(2);

            $arrayData = array();

            if (!empty($data)) {
                $arrayData = json_decode($data, true);
            }

            return $arrayData;

        }

        public function saveData($exchange, $area, $data, $currency) {

            if (!is_dir(BASE_PATH . "/data")) {
                mkdir(BASE_PATH . "/data",0777);
            }

            if (!is_dir(BASE_PATH . "/data/resync")) {
                mkdir(BASE_PATH . "/data/resync",0777);
            }

            if (!is_dir(BASE_PATH . "/data/resync/data")) {
                mkdir(BASE_PATH . "/data/resync/data",0777);
            }

            $dirBaseData = BASE_PATH."/data/resync/data";

            $fileName = date("YmdHi");
            $fileData = $dirBaseData . "/$exchange-$area-$currency-" . $fileName . ".json";

            if (!file_exists($fileData) && !empty($data)) {

                // var_dump($data);

                file_put_contents($fileData, json_encode($data));
                if (file_exists($fileData)) {
                    return $fileData;
                } else {
                    return false;
                }

            } else {

                return false;

            }

        }

        public function showResume($arrayData, $exchange, $type, $currency, $starttime, $file=false, $hash, $typeReturn="temp", $date_expire=false) {

            $endtime = microtime(true);
            $timediff = $endtime - $starttime;
            $elapsed_time = secondsToTime($timediff);

            $date = date("Y-m-d H:i:s");

            $totalData = ($arrayData) ? count($arrayData) : 0;

            $status = ($file) ? "sucess" : "error";

            $action = $exchange . "|" . $currency . "|" . $type . "|" . $date;

            switch($typeReturn) {
                case "temp":

                    echoColor("--------------------------------------------------------");
                    echoDefault("Date: " . $date);
                    echoDefault("Action: $action");
                    echoDefault("Elapsed time: " . $elapsed_time);
                    echoDefault("Total: " . $totalData);
                    echoDefault("Type: $type");
                    echoDefault("Status: $status");
                    if ($hash) { echoDefault("hash: $hash"); }
                    if ($date_expire) { echoDefault("Date Expire: $date_expire"); }
                    echoDefault("Exchange: $exchange");
                    if ($file) { echoDefault("File saved: $file"); }
                    echoColor("--------------------------------------------------------");

                    echo "";

                break;
                case "json":

                    $arrayData = array();
                    $arrayData["date"] = $date;
                    $arrayData["action"] = $action;
                    $arrayData["elapsed_time"] = $elapsed_time;
                    $arrayData["total"] = $totalData;
                    $arrayData["type"] = $type;
                    $arrayData["status"] = $status;
                    if ($hash) { $arrayData["hash"] = $hash; }
                    if ($date_expire) { $arrayData["date_expire"] = $date_expire; }
                    $arrayData["date_now"] = date("Y-m-d H:i:s");
                    $arrayData["exchange"] = $exchange;

                    if ($file) {
                        $arrayData["file_saved"] = $file;
                    }

                    echo json_encode($arrayData);

                break;
            }

        }

    }

?>