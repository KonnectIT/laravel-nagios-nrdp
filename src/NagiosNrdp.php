<?php

namespace KonnectIT\LaravelNagiosNrdp;

class NagiosNrdp
{
    /** @var string $host */
    protected $url = "";

    /** @var string $token */
    protected $token = "";

    /** @var string $host */
    protected $host = "";

    /** @var string $service */
    protected $service = "";

    /** @var int $state */
    protected $state = 0; // OK (service) / UP (host)

    /** @var string $message */
    protected $message = "";

    /** @var string $type */
    protected $type = "host";

    /** @var int $checktype */
    protected $checktype = 1; // Passive check

    /**
     * Create a new Skeleton Instance.
     */
    public function __construct()
    {
        $this->url = config('laravel-nagios-nrdp.url');
        $this->host = config('laravel-nagios-nrdp.host');
        $this->token = config('laravel-nagios-nrdp.token');
    }

    /**
     * @param int $state
     * @return NagiosNrdp
     */
    public function state(int $state): NagiosNrdp
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return NagiosNrdp
     */
    public function host(): NagiosNrdp
    {
        $this->type = 'host';

        return $this;
    }

    /**
     * @param string $service
     * @return NagiosNrdp
     */
    public function service($service = ''): NagiosNrdp
    {
        $this->service = $service;
        $this->type = 'service';

        return $this;
    }

    /**
     * @param string $message
     * @return array|false|string
     * @throws \Exception
     */
    function send(string $message = '')
    {
        $this->message = $message;

        if ($this->service != "") {
            $this->type = "service";
        }

        $this->parameterVerification();

        $hostchecks = array();
        $servicechecks = array();

        // Process single check from command line
        if ($this->host != "") {
            if ($this->service != "") {
                // Service check
                $newc = array(
                    "hostname" => $this->host,
                    "servicename" => $this->service,
                    "state" => $this->state,
                    "output" => $this->message
                );
                $servicechecks[] = $newc;
            } else {
                // Host check
                $newc = array(
                    "hostname" => $this->host,
                    "state" => $this->state,
                    "output" => $this->message
                );
                $hostchecks[] = $newc;
            }
        }

        $xml = $this->craftXML($hostchecks, $servicechecks);

        // Build URL
        $url = $this->url . "/?token=" . $this->token . "&cmd=submitcheck&xml=" . urlencode($xml);

        // Send data to NRDP
        $opts = array(
            "method" => "post",
            "timeout" => 30,
            "return_info" => true
        );

        $result = $this->load_url($url, $opts);

        return $result;
    }

    /**
     * Renamed to load_url
     *
     * See http://www.bin-co.com/php/scripts/load/
     * Version : 1.00.A
     * License: BSD
     * @param $url
     * @param array $options
     * @return array|false|string
     */
    protected function load_url($url, $options = array('method' => 'get', 'return_info' => false))
    {
        // Added default timeout of 15 seconds
        if (!isset($options['timeout'])) {
            $options['timeout'] = 15;
        }

        $url_parts = parse_url($url);
        if (!empty($url_parts['port'])) {
            $port = intval($url_parts['port']);
        } else {
            // Default to port 80
            $port = 80;
            if (isset($url_parts['scheme']) && preg_match("#https#i", $url_parts['scheme']) == 1) {
                $port = 443;
            }
        }

        // Currently only supported by curl
        $info = array('http_code' => 200);
        $response = '';

        $send_header = array(
            'Accept' => 'text/*',
            'User-Agent' => 'BinGet/1.00.A (http://www.bin-co.com/php/scripts/load/)'
        );

        ///////////////////////////// Curl /////////////////////////////////////
        // If curl is available, use curl to get the data and don't use curl if
        // it is specifically stated to use fsocketopen in the options
        if (function_exists("curl_init") and (!(isset($options['use']) and $options['use'] == 'fsocketopen'))) {

            if (isset($options['method']) and $options['method'] == 'post') {
                $page = $url_parts['scheme'] . '://' . $url_parts['host'] . ':' . $port . $url_parts['path'];
            } else {
                $page = $url;
            }

            $ch = curl_init();

            // Added a timeout
            if (isset($options['timeout'])) {
                curl_setopt($ch, CURLOPT_TIMEOUT, $options['timeout']);
            }

            curl_setopt($ch, CURLOPT_URL, $page);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Just return the data - not print the whole thing.
            curl_setopt($ch, CURLOPT_HEADER, true); // We need the headers
            curl_setopt($ch, CURLOPT_NOBODY, false); // The content - if true, will not download the contents
            if (isset($options['method']) and $options['method'] == 'post' and $url_parts['query']) {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $url_parts['query']);
            }

            //Set the headers our spiders sends
            curl_setopt($ch, CURLOPT_USERAGENT, $send_header['User-Agent']); // The Name of the UserAgent we will be using ;)
            $custom_headers = array("Accept: " . $send_header['Accept']);
            if (isset($options['modified_since'])) {
                array_push($custom_headers, "If-Modified-Since: " . gmdate('D, d M Y H:i:s \G\M\T', strtotime($options['modified_since'])));
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $custom_headers);

            curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie.txt");
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

            if (isset($url_parts['user']) and isset($url_parts['pass'])) {
                $custom_headers = array("Authorization: Basic " . base64_encode($url_parts['user'] . ':' . $url_parts['pass']));
                curl_setopt($ch, CURLOPT_HTTPHEADER, $custom_headers);
            }

            $response = curl_exec($ch);
            $info = curl_getinfo($ch); // Some information on the fetch
            curl_close($ch);

            //////////////////////////////////////////// FSockOpen //////////////////////////////
        } else {
            // If there is no curl, use fsocketopen

            if (isset($url_parts['query'])) {
                if (isset($options['method']) and $options['method'] == 'post') {
                    $page = $url_parts['path'];
                } else {
                    $page = $url_parts['path'] . '?' . $url_parts['query'];
                }
            } else {
                $page = $url_parts['path'];
            }

            $fp = fsockopen($url_parts['host'], $port, $errno, $errstr, 30);
            if ($fp) {

                // Added a timeout
                if (isset($options['timeout'])) {
                    stream_set_timeout($fp, $options['timeout']);
                }

                $out = '';
                if (isset($options['method']) and $options['method'] == 'post' and isset($url_parts['query'])) {
                    $out .= "POST $page HTTP/1.1\r\n";
                } else {
                    $out .= "GET $page HTTP/1.0\r\n"; // HTTP/1.0 is much easier to handle than HTTP/1.1
                }
                $out .= "Host: $url_parts[host]\r\n";
                $out .= "Accept: $send_header[Accept]\r\n";
                $out .= "User-Agent: {$send_header['User-Agent']}\r\n";
                if (isset($options['modified_since'])) {
                    $out .= "If-Modified-Since: " . gmdate('D, d M Y H:i:s \G\M\T', strtotime($options['modified_since'])) . "\r\n";
                }

                $out .= "Connection: Close\r\n";

                // HTTP Basic Authorization support
                if (isset($url_parts['user']) and isset($url_parts['pass'])) {
                    $out .= "Authorization: Basic " . base64_encode($url_parts['user'] . ':' . $url_parts['pass']) . "\r\n";
                }

                // If the request is post - pass the data in a special way.
                if (isset($options['method']) and $options['method'] == 'post' and $url_parts['query']) {
                    $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
                    $out .= 'Content-Length: ' . strlen($url_parts['query']) . "\r\n";
                    $out .= "\r\n" . $url_parts['query'];
                }
                $out .= "\r\n";

                fwrite($fp, $out);
                while (!feof($fp)) {
                    $response .= fgets($fp, 128);
                }
                fclose($fp);
            }
        }

        // Get the headers in an associative array
        $headers = array();

        if ($info['http_code'] == 404) {
            $body = "";
            $headers['Status'] = 404;
        } else {
            $separator_position = strpos($response, "\r\n\r\n");
            $header_text = substr($response, 0, $separator_position);
            $body = substr($response, $separator_position + 4);

            // If we get a 301 (moved), another set of headers is received
            if (substr($body, 0, 5) == "HTTP/") {
                $separator_position = strpos($body, "\r\n\r\n");
                $header_text = substr($body, 0, $separator_position);
                $body = substr($body, $separator_position + 4);
            }

            foreach (explode("\n", $header_text) as $line) {
                $parts = explode(": ", $line);
                if (count($parts) == 2) $headers[$parts[0]] = chop($parts[1]);
            }
        }

        if ($options['return_info'])
            return array('headers' => $headers, 'body' => $body, 'info' => $info);
        return $body;
    }

    /**
     * @param array $hostchecks
     * @param array $servicechecks
     * @return string
     */
    protected function craftXML(array $hostchecks, array $servicechecks): string
    {
        $checkresultopts = "";
        $checkresultopts = " checktype='" . $this->checktype . "'";
        $xml = "<?xml version='1.0'?> 
<checkresults>
";

        // Add each host check
        foreach ($hostchecks as $hc) {
            $hostname = $hc["hostname"];
            $state = $hc["state"];
            $output = $hc["output"];

            $xml .= "
    <checkresult type='host' " . $checkresultopts . ">
        <hostname>" . htmlentities($hostname) . "</hostname>
        <state>" . $state . "</state>
        <output>" . htmlentities($output) . "</output>
    </checkresult>
        ";
        }

        // Add each service check
        foreach ($servicechecks as $sc) {
            $hostname = $sc["hostname"];
            $servicename = $sc["servicename"];
            $state = $sc["state"];
            $output = $sc["output"];

            $xml .= "
    <checkresult type='service' " . $checkresultopts . ">
        <hostname>" . htmlentities($hostname) . "</hostname>
        <servicename>" . htmlentities($servicename) . "</servicename>
        <state>" . $state . "</state>
        <output>" . htmlentities($output) . "</output>
    </checkresult>
        ";
        }

        $xml .= "
</checkresults>";

        return $xml;
    }

    /**
     * @throws Exception
     */
    protected function parameterVerification()
    {
        if (empty($this->url)) {
            throw new \Exception('Nagios URL is not set');
        }
        if (empty($this->token)) {
            throw new \Exception('Nagios token is not set');
        }
        if (empty($this->host)) {
            throw new \Exception('Nagios host is not set');
        }
        if (!is_numeric($this->state)) {
            throw new \Exception('Nagios state is not numeric');
        }
        if (empty($this->message)) {
            throw new \Exception('Nagios message is not set');
        }
    }

}
