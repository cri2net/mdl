<?php

/**
* Реализация протокола OAuth 2.0
* @see https://drive.google.com/file/d/1SsEwlMCqD1NU0-F5R3uQ-thYdJsXPCVg/view [Описание протокола]
*/
class OpenId
{
    const AUTHORIZE_URL = 'https://id2.kyivcity.gov.ua/authorize';

    const TOKEN_URL     = 'https://id2.kyivcity.gov.ua/token';

    const USERINFO_URL  = 'https://id2.kyivcity.gov.ua/userinfo';

    const EMR_QUERY_URL = 'https://emr-query.kyivcity.gov.ua/profile/query/api/v1/query';

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
        'profile',
        'profile.basic',
        'profile.emails',
        'profile.phones',
        'phone',
    ];

    public function __construct()
    {
        $this->secret = '';
    }

    public function getState()
    {
        return $this->state;
    }

    /**
     * Отправка готового запроса к API
     * @param  string $query   Текст запроса в GraphQL
     * @param  string $token   Токен доступа
     * @return StdClass object Ответ от API
     */
    public static function rawRequest($query, $token)
    {
        $data = json_encode([
            'query' => $query,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $response = self::sendCurlRequest($data, $token);

        return $response;
    }

    /**
     * Отправляет подготовленный запрос к API через cURL
     * @param  string $data  JSON строка с запросом
     * @param  string $url   Ссылка на API
     * @param  string $token Токен для доступа к API
     * @return string        Ответ от API
     */
    protected static function sendCurlRequest($data, $token)
    {
        $url = self::EMR_QUERY_URL;

        $headers = [];
        $headers[] = 'Content-Type: application/json';
        $headers[] = "Authorization: Bearer $token";

        $ch = curl_init();
        $options = [
            CURLOPT_URL            => $url,
            CURLOPT_HEADER         => false,
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS     => $data,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => $headers,
        ];
        
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public function getInfoFromEmr($user_id, $token)
    {
        $user_id = (int)$user_id;
        $query = "{profile(id: $user_id) {id name {lastName firstName middleName } phones {phoneNumber confirmed type } emails {email confirmed type } } }";

        $response = self::rawRequest($query, $token);
        return $response;
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
