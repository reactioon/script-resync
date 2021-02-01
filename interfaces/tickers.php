<?php

    /**
     * Interface for currency
     * @author José Wilker <jose.wilker@smartapps.com.br>
     */

    interface koinsTicker {

        public function __construct($koin);

        // get all symbols
        public function getTickers($arraySymbols, $exchange, $pair);
        // public function getBySymbol($symbol);

    }

?>