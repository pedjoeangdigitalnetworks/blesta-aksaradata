<?php

class AksaraDataClient
{
    private $api_base = "http://api6.irsfa.id";

    public function __construct($api_base = null)
    {
        if ($api_base) {
            $this->api_base = $api_base;
        }
    }

    private function message($code, $msg)
    {
        return [
            "code" => $code,
            "message" => $msg,
        ];
    }

    private function messageWithData($code, $msg, $data)
    {
        return [
            "code" => $code,
            "message" => $msg,
            "data" => $data,
        ];
    }

    private function _request($method, $url, $oauth2, $data = null)
    {
        $curl = curl_init();

        $headers = array(
            "Authorization: Bearer " . $oauth2,
            "Content-Type: application/x-www-form-urlencoded",
            "X-Requested-With: XMLHttpRequest",
            "Accept: application/json",
        );

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->api_base . $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 90,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_POSTFIELDS => http_build_query($data),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            throw new Exception($err);
        } else {
            return json_decode($response);
        }
    }

    private function authentication($data)
    {
        $response = $this->_request("POST", "/oauth/token", null, $data);

        if (isset($response->access_token)) {
            return $response->access_token;
        } else {
            throw new Exception($response->message);
        }
    }

    public function registerDomain($params)
    {
        $oauth2 = [
            "grant_type" => "client_credentials",
            "client_id" => $params['clientid'],
            "client_secret" => $params['secretid'],
            "scope" => "",
        ];

        $data = [
            "domain" => $params['sld'] . "." . $params['tld'],
            "period" => $params['regperiod'],
            "nameserver" => [$params['ns1'], $params['ns2'], $params['ns3'], $params['ns4'], $params['ns5']],
            "description" => "Blesta Register Domain [New Module]",
            "domain_name" => $params['sld'],
            "domain_extension" => $params['tld'],
        ];

        $registrant = array(
            'company_name' => $params['companyname'],
            'initial' => substr($params['firstname'], 0, 1) . substr($params['lastname'], 0, 1),
            'first_name' => $params['firstname'],
            'last_name' => $params['lastname'],
            'gender' => 'M',
            'street' => $params['address1'],
            'street2' => $params['address2'],
            'number' => 13,
            'city' => $params['city'],
            'state' => $params['state'],
            'zip_code' => $params['postcode'],
            'country' => $params['country'],
            'email' => $params['email'],
            'telephone_number' => str_replace('.', '', $params['fullphonenumber']),
            'locale' => 'en_GB',
        );

        if (($data['nameserver'][0] == "")) {
            unset($data['nameserver']);
        } else {
            foreach ($data['nameserver'] as $key => $value) {
                if (empty($value)) {
                    unset($data['nameserver'][$key]);
                }
            }
        }

        $datas = array_merge($data, $registrant);

        try {
            $auth = $this->authentication($oauth2);
            $request = $this->_request("POST", "/api/rest/v3/domain/register", $auth->access_token, $datas);

            if ($request->code !== 200) {
                return ["error" => $request->message];
            } else {
                return $this->message($request->code, $request->message);
            }
        } catch (\Exception $e) {
            return array(
                'error' => $e->getMessage(),
            );
        }
    }

    function transferDomain($params) {
        $oauth2 = [
            "grant_type" => "client_credentials",
            "client_id" => $params['clientid'],
            "client_secret" => $params['secretid'],
            "scope" => "",
        ];
    
        $data = [
            "domain"      => $params['sld'].".".$params['tld'],
            "auth_code"   => $params['eppcode'],
            "period"      => $params['regperiod'],
            "nameserver"  => [$params['ns1'], $params['ns2'], $params['ns3'], $params['ns4'], $params['ns5']],
            "description" => "[WHMCS] Transfer Domain [New Module]",
            "epp"         => $params['eppcode'],
            "domain_name" => $params['sld'],
            "domain_extension" => $params['tld'],
        ];
    
       $registrant = array(
            'company_name'     => $params['companyname'],
            'initial'          => substr($params['firstname'],0,1).substr($params['lastname'],0,1),
            'first_name'       => $params['firstname'],
            'last_name'        => $params['lastname'],
            'gender'           => 'M',
            'street'           => $params['address1'],
            'street2'          => $params['address2'],
            'number'           => 13,
            'city'             => $params['city'],
            'state'            => $params['state'],
            'zip_code'         => $params['postcode'],
            'country'          => $params['country'],
            'email'            => $params['email'],
            'telephone_number' => str_replace('.','',$params['fullphonenumber']),
            'locale'           => 'en_GB'
          );
    
        if(($data['nameserver'][0] == "")){
            unset($data['nameserver']);
        }else{
            foreach($data['nameserver'] as $key => $value){
                if (empty($value)) {
                    unset($data['nameserver'][$key]);
                 }
            }
        }
    
        $datas = array_merge($data,$registrant);
    
        try {
            $auth = $this->authentication($oauth2);
            $request = $this->_request("POST", "/api/rest/v3/domain/transfer", $auth->access_token, $datas);

            if ($request->code !== 200) {
                return ["error" => $request->message];
            } else {
                return $this->message($request->code, $request->message);
            }
        } catch (\Exception $e) {
            return array(
                'error' => $e->getMessage(),
            );
        }
    }

    function renewDomain($params) {
        $oauth2 = [
            "grant_type" => "client_credentials",
            "client_id" => $params['clientid'],
            "client_secret" => $params['secretid'],
            "scope" => "",
        ];
    
        $datas = [
            "domain"         => $params['sld'].".".$params['tld'],
            "period"         => $params['regperiod'],
            "description"    => "[WHMCS] Renew Domain [New Module]",
            "domain_name"    => $params['sld'],
            "domain_extension" => $params['tld'],
        ];
    
        try {
            $auth = $this->authentication($oauth2);
            $request = $this->_request("POST", "/api/rest/v3/domain/transfer", $auth->access_token, $datas);

            if ($request->code !== 200) {
                return ["error" => $request->message];
            } else {
                return $this->message($request->code, $request->message);
            }
        } catch (\Exception $e) {
            return array(
                'error' => $e->getMessage(),
            );
        }
    }

    function getEppCode($params) {
        $oauth2 = [
            "grant_type" => "client_credentials",
            "client_id" => $params['clientid'],
            "client_secret" => $params['secretid'],
            "scope" => "",
        ];
    
        $datas = [
            "domain"      => $params['sld'].".".$params['tld'],
            "domain_name" => $params['sld'],
            "domain_extension" => $params['tld'],
        ];

        try {
            $auth = $this->authentication($oauth2);
            $request = $this->_request("POST", "/api/rest/v3/domain/eppcode", $auth->access_token, $datas);

            if ($request->code !== 200) {
                return ["error" => $request->message];
            } else {
                return $this->message($request->code, $request->message);
            }
        } catch (\Exception $e) {
            return array(
                'error' => $e->getMessage(),
            );
        }
    }

    function getRegistrarLock($params) {
        $oauth2 = [
            "grant_type" => "client_credentials",
            "client_id" => $params['clientid'],
            "client_secret" => $params['secretid'],
            "scope" => "",
        ];
    
        $datas = [
            "domain"      => $params['sld'].".".$params['tld'],
            "domain_name" => $params['sld'],
            "domain_extension" => $params['tld'],
        ];

        try {
            $auth = $this->authentication($oauth2);
            $request = $this->_request("POST", "/api/rest/v3/domain/info", $auth->access_token, $datas);

            if($request->code !== 200){
                return ["error" => $request->message];
            }else{
                $status = $request->data->thief_protection == 1 ? 'locked':'unlocked';
                return $status;
            }
        } catch (\Exception $e) {
            return array(
                'error' => $e->getMessage(),
            );
        }
    }

    function settRegistrarLock($params) {
        $oauth2 = [
            "grant_type" => "client_credentials",
            "client_id" => $params['clientid'],
            "client_secret" => $params['secretid'],
            "scope" => "",
        ];
    
        $datas = [
            "domain"         => $params['sld'].".".$params['tld'],
            "domain_name"    => $params['sld'],
            "domain_extension" => $params['tld'],
            "status"         => ($params['lockenabled'] == 'locked') ? 1 : 0 
        ];

        try {
            $auth = $this->authentication($oauth2);
            $request = $this->_request("POST", "/api/rest/v3/domain/lock", $auth->access_token, $datas);

            if($request->code !== 200){
                return ["error" => $request->message];
            }else{
                $status = $request->data->thief_protection == 1 ? 'locked':'unlocked';
                return $status;
            }
        } catch (\Exception $e) {
            return array(
                'error' => $e->getMessage(),
            );
        }
    }
}
