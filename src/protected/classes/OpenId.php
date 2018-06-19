<?php

/**
* Реализация протокола OAuth 2.0
* @see https://drive.google.com/file/d/1SsEwlMCqD1NU0-F5R3uQ-thYdJsXPCVg/view [Описание протокола]
*/
class OpenId
{
    const AUTHORIZE_URL = 'https://openid.egp.com.ua/authorize';

    const TOKEN_URL     = 'https://openid.egp.com.ua/token';

    const USERINFO_URL  = 'https://openid.egp.com.ua/userinfo';

    public $clientId;

    public $secret;

    public $redirect_uri;

    protected $state;
    
    public $scopes = [
        'openid',
        'email',
        'offline_access',
        'orders.create',
        'orders.status.update',
        // 'address',
        // 'profile',
        // 'phone',
    ];

    public function __construct()
    {
        $this->secret = '';
    }

    public function getState()
    {
        return $this->state;
    }

    public function getAuthorizationUrl()
    {
        if (empty($this->getState())) {
            $this->setState();
        }

        $url = self::AUTHORIZE_URL;
        $url .= '?response_type=code';
        $url .= '&client_id=' . $this->clientId;
        $url .= '&scope=' . implode('%20', $this->scopes);
        $url .= '&redirect_uri=' . $this->redirect_uri;
        $url .= '&state=' . $this->getState();
        $url .= '&nonce=' . generateCode(15);

        return $url;
    }

    /**
     * Получение данных о токене по коду (шаг 3 инструкции)
     * 
     * @see https://drive.google.com/file/d/1SsEwlMCqD1NU0-F5R3uQ-thYdJsXPCVg/view [Описание протокола]
     * @param  string $code Код авторизации
     * @return string       json строка с ответом
     */
    public function getAccessToken($code)
    {
        $url = self::TOKEN_URL;
        $data = [
            'grant_type'   => 'authorization_code',
            'redirect_uri' => $this->redirect_uri,
            'code'         => $code,
        ];

        $ch = curl_init();
        $options = [
            CURLOPT_URL            => $url,
            CURLOPT_HEADER         => false,
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS     => http_build_query($data),
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Basic ' . base64_encode("{$this->clientId}:{$this->secret}"),
                'Content-Type: application/x-www-form-urlencoded',
            ],
        ];
        
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public function setState($new_state = null)
    {
        if ($new_state === null) {
            $new_state = generateCode(15);
        }
        $this->state = $new_state;
    }

    public function unsetState()
    {
        $this->state = null;
    }
}
