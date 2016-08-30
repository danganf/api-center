<?php

namespace Ufox;

class ApiCenter
{
    public function getCep($cep){
        return $this->curl( $this->getUrl( 'consulta_cep' ) . $cep );
    }

    public function getIp($ip){
        return $this->curl( $this->getUrl( 'consulta_ip' ) . $ip );
    }

    public function getOperadora($telefone){
        return $this->curl( $this->getUrl( 'consulta_operadora' ) . $telefone );
    }

    public function getVencimentoOperadora($operadora){
        return $this->curl( $this->getUrl( 'consulta_vencimento' ) . $operadora );
    }

    public function getInfoCPF($cpf){
        return $this->curl( $this->getUrl( 'consulta_cpf' ) . $cpf );
    }

    public function getInfoCodRastreamento( $codigo ){
        return $this->curl( $this->getUrl( 'consulta_cod_rastreamento' ) . $codigo );
    }

    public function getPlanos( $ddd, $tipo = 'controle', $operadora = 'VIVO' ){
        return $this->curl( $this->getUrl( 'consulta_planos' ) . "?operadora=$operadora&ddd=$ddd&tipo=$tipo" );
    }

    public function getStoreMagento(){
        return $this->curl( $this->getUrl( 'get_store' ) );
    }

    public function getEstados(){
        return $this->curl( $this->getUrl( 'estados' ) );
    }

    public function getUFPorDDD(){
        return $this->curl( $this->getUrl( 'uf_por_ddd' ) );
    }

    public function getDDDPorUf(){
        return $this->curl( $this->getUrl( 'ddd_por_uf' ) );
    }

    public function getValidaDados( $cpf, $nome, $mae, $dataNasc ){
        return $this->curl( $this->getUrl( 'valida_dados' ), [
            'json' => true,
            'post' => true,
            'data' => json_encode( [ 'cpf'=>$cpf, 'nome_completo'=>$nome, 'mae'=>$mae, 'data_nascimento'=>$dataNasc ] )
        ] );
    }

    public function createOrderMagento( $stringJson ){
        $url = str_replace( 'manager_laravel.app','127.0.0.6:8000',$this->getUrl( 'envia_pedido' ) );#php artisan serve --host=127.0.0.6
        return $this->curl($url, [ 'json' => true, 'post' => true, 'data' => $stringJson ] );
    }

    public function backEndGetSessionId(){
        $retorno = $this->curl( $this->getUrl( 'backend_api' ) . '/get-session-id', ['backend'=>TRUE] );
        return ( isset( $retorno['session_id'] ) ? $retorno['session_id'] : null );
    }

    public function backEndSaveRegistro( $sessionID, $arrayValores ){
        $json = json_encode( $arrayValores );
        return $this->curl( $this->getUrl( 'backend_api' ) . '/registra', [ 'json' => true, 'post' => true, 'data' => $json, 'backend'=>$sessionID ] );
    }

    public function backEndCreateOrder( $sessionID ){
        return $this->curl( $this->getUrl( 'backend_api' ) . '/create', [ 'post' => true, 'backend'=>$sessionID ] );
    }

    public function backEndgetInfoOrder( $sessionID ){
        return $this->curl( $this->getUrl( 'backend_api' ) . '/get', [ 'post' => true, 'backend'=>$sessionID ] );
    }

    public function getIpPermission( $ip, $site ){
        return $this->curl( $this->getUrl( 'ip_permission' ), [
            'json' => true,
            'post' => true,
            'data' => json_encode( [ 'ip'=>$ip, 'site'=>$site ] )
        ] );
    }

    public function getOrderCustomer( $cpf, $codigoPedido=null, $nascimento=null ){
        return $this->curl( $this->getUrl( 'get_order_customer' ), [
            'json' => true,
            'post' => true,
            'data' => json_encode( [ 'cpf'=>$cpf, 'codigo_pedido'=>$codigoPedido, 'nascimento'=>$nascimento ] )
        ] );
    }

    public function getCheckSMSOnLine(){
        $ret   = $this->curl( $this->getUrl( 'verify_sms' ) );
        $flag  = TRUE;
        if( isset( $ret['status'] ) ) {
            $flag = $ret['status'];
        }
        return $flag;
    }

    public function senEmail( $codTemplate, $nome, $email, $outrosDados = [] ){

        $dados['cod_template']          = $codTemplate;
        $dados['destinatario']['nome']  = $nome;
        $dados['destinatario']['email'] = $email;
        $dados['data']                  = $outrosDados;

        return $this->curl( $this->getUrl( 'envia_email' ), [
            'json' => true,
            'post' => true,
            'data' => json_encode( $dados )
        ] );
    }

    public function getUrl($apiName){
        $retorno = $this->curl( config('app.url_api_center').$apiName.'/'.config('app.env') );
        return $retorno['url'];

    }

    private function parseReturn( $jsonString ){

        $json = json_decode( $jsonString, TRUE );
        $retorno = FALSE;
        if( !isset( $json['error'] ) ) {
            $retorno = $json;
        }
        return $retorno;

    }

    private function curl ($url, array $options = [])
    {
        $timeout           = 5;
        $connectionTimeout = 3;

        $ch = curl_init();

        if (count($options) > 0) {

            if (!empty($options['timeout']))
                $timeout = $options['timeout'];

            if (!empty($options['connectionTimeout']))
                $connectionTimeout = $options['connectionTimeout'];

            if (!empty($options['header'])) {
                if (is_array($options['header'])) {
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $options['header']);
                } else {
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array($options['header']));
                }
            }

            if (!empty($options['method']))
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $options['method']);

            if (!empty($options['post']))
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

            if (!empty($options['data']))
                curl_setopt($ch, CURLOPT_POSTFIELDS, $options['data']);

            if (!empty($options['json'])) {
                $dados[] = 'Content-Type: application/json';
                $dados[] = 'Content-Length: ' . strlen($options['data']);
            }

            if ( !empty( $options['backend'] ) ) {
                $dados[] = 'api-token: '.config('app.api_token');
                if( !is_bool( $options['backend'] ) ) {
                    $dados[] = 'session-id: '.$options['backend'];
                }
            }

            if( isset( $dados ) ){
                curl_setopt($ch, CURLOPT_HTTPHEADER, $dados);
            }
        }

        $refer = ( isset( $_SERVER['HTTP_X_ALT_REFERER'] ) ? $_SERVER['HTTP_X_ALT_REFERER'] : ( isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : \Request::server('HTTP_REFERER') ) );

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connectionTimeout);
        curl_setopt($ch, CURLOPT_REFERER, $refer );

        $result = curl_exec($ch);
        if( strpos($url,'get-url-api') === FALSE ) {
            //var_dump($url.' > '.$result);
        }
        curl_close($ch);

        return $this->parseReturn( $result );
    }
}