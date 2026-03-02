<?php

if (! function_exists('api_stream_json')) {
    /**
     * Return JSON response using stream download for better performance with large responses.
     * This function bypasses output buffering issues and is optimized for large JSON responses.
     *
     * @param  string  $jsonContent  Pre-serialized JSON string
     * @param  string|null  $filename  Optional filename for download (default: 'response.json')
     * @param  array  $headers  Additional headers to set
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    function api_stream_json(string $jsonContent, ?string $filename = null, array $headers = [])
    {
        $defaultHeaders = [
            'Content-Type' => 'application/json; charset=utf-8',
        ];

        $headers = array_merge($defaultHeaders, $headers);

        return response()->streamDownload(
            function () use ($jsonContent) {
                echo $jsonContent;
            },
            $filename ?? 'response.json',
            $headers
        );
    }
}
