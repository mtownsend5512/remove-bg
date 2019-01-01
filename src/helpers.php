<?php

if (!function_exists('removebg')) {
    /**
     * Create a new instance of the Mtownsend\RemoveBg\RemoveBg class
     *
     * @return Mtownsend\RemoveBg\RemoveBg
     */
    function removebg($data = [])
    {
        if (!isset($data['api_key'])) {
            $data['api_key'] = config('removebg.api_key');
        }
        if (!isset($data['headers'])) {
            $data['headers'] = [];
        }
        return app()->make('removebg', $data);
    }
}
