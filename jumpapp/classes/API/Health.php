<?php

namespace Jump\API;

class Health extends AbstractAPI {

    public function get_output(): string {
        // We made it here so output the API response as json.
        header('Content-Type: application/json');
        return 200;
    }

}
