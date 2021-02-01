<?php

    /**
     * Interface for currency
     * @author José Wilker <jose.wilker@smartapps.com.br>
     */

    interface koinsCandles {

        public function __construct($koin);

        public function getPrices($symbol, $candles, $start, $end=false, $tickSize);
        public function getMonth($bin, $arraySymbols, $exchange);
        public function getDay($bin, $arraySymbols, $exchange);
        public function getDataMonth($symbol, $tickSize);
        public function getDataDay($symbol, $tickSize);

        public function getCandlesLastBySymbol($symbol);

    }

?>