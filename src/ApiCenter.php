<?php

namespace Ufox;

class ApiCenter
{
    private $timeCache = 720;

    public function getUrlBasic( $name ){

        $value = \Cache::remember("ApiCenter_$name", $this->timeCache, function() use($name) {
            return $this->getUrl( $name );
        });

        return $value;

    }

    public function getCep( $cep ){ return $this->curl( $this->getUrlBasic( 'central_api' ) . "/consulta-cep/$cep" ); }
    public function getIp( $ip ){ return $this->curl( $this->getUrlBasic( 'central_api' ) . "/consulta-ip/$ip" ); }
    public function getOperadora( $telefone ){ return $this->curl( $this->getUrlBasic( 'central_api' ) . "/consulta-operadora/$telefone" ); }
    public function getInfoCPF( $cpf ){ return $this->curl( $this->getUrlBasic( 'consulta_cpf' ) . $cpf ); }
    public function getDetalhesSku( $sku ){ return $this->curl( $this->getUrlBasic( 'consulta_detalhe' ) .'?skus='. $sku ); }
    public function getInfoCodRastreamento( $codigo ){ return $this->curl( $this->getUrlBasic( 'central_api' ) . "/get-status-correios/$codigo" ); }
    public function getPlanos( $ddd, $tipo = 'controle', $operadora = 'VIVO' ){ return $this->curl( $this->getUrlBasic( 'consulta_planos' ) . "?operadora=$operadora&ddd=$ddd&tipo=$tipo" ); }
    public function getStoreMagento(){ return $this->curl( $this->getUrlBasic( 'api_mag' ) . '/get-store' ); }
    public function getInfoPromocaoLP( $randKey ){ return $this->curl( $this->getUrlBasic( 'api_mag' ) . "/promocao/get/$randKey" ); }
    public function getPromoGigaPassNoProcess(){ return $this->curl( $this->getUrlBasic( 'api_mag' ) . "/promocao/get-no-process"); }
    public function getPromoGigaPassNoProcessTotal(){ return $this->curl( $this->getUrlBasic( 'api_mag' ) . "/promocao/get-no-process/total"); }
    public function getPromoGigaPassDetail( $orderID ){ return $this->curl( $this->getUrlBasic( 'api_mag' ) . "/promocao/get-detail/$orderID"); }
    public function getChips( $operadora = 'VIVO' ){ return $this->curl( $this->getUrlBasic( 'consulta_chip' ) . $operadora ); }
    public function sendMOLFila( $stringJson ){ return $this->curl($this->getUrlBasic( 'send_mol' ), [ 'json' => true, 'post' => true, 'data' => $stringJson ] ); }
    public function createOrderMagento( $stringJson ){ return $this->curl( $this->getUrlBasic( 'envia_pedido' ) , [ 'json' => true, 'post' => true, 'data' => $stringJson ] ); }
    public function getAbandonosModalVendaOnLineVivo(){ return $this->curl( $this->getUrlBasic( 'backend_api' ) . '/get-abandono' ); }
    public function setProcessadoAbandonoModalVendaOnLineVivo( $leadID, $linhaServico ){ return $this->curl( $this->getUrlBasic( 'backend_api' ) . '/set-processado-abandono/' . $leadID . '/' . $linhaServico ); }
    public function getDetailOrder( $linha ){ return $this->curl( $this->getUrlBasic( 'detail_order' ) . $linha ); }
    public function getStoreInfo($storeID){ return $this->curl( $this->getUrlBasic( 'api_mag' ) .'/store-info/'. $storeID ); }

    public function getCodigoUF( $uf )
    {
        $result = $this->curl( $this->getUrlBasic( 'central_api' ) . "/uf-codigo/$uf" );
        return ( !empty( $result ) ? $result['codigo'] : NULL );
    }

    public function converteNonoDigito( $ddd, $linha ){

        if( strlen($linha) == 8 ) {

            $result = $this->curl( $this->getUrlBasic('central_api') . "/consulta-nono-digito/$ddd$linha" );
            if( !empty( $result ) ){
                $linha = $result['linha'];
                $linha = substr( $linha, 2, strlen( $linha ) );
            }
        }

        return $linha;
    }

    public function getVencimentoOperadora($operadora){

        $value = \Cache::remember("VencimentoOperadora_$operadora", $this->timeCache, function() use($operadora) {
            return $this->curl( $this->getUrl( 'consulta_vencimento' ) . $operadora );
        });

        return $value;
    }

    public function getEstados(){

        $value = \Cache::remember('getEstados', 360, function() {
            return $this->curl( $this->getUrl( 'estados' ) );
        });

        return $value;
    }

    public function getUFPorDDD(){

        $value = \Cache::remember('getUFPorDDD', 360, function() {
            return $this->curl( $this->getUrl( 'uf_por_ddd' ) );
        });

        return $value;
    }

    public function getDDDPorUf(){

        $value = \Cache::remember('getDDDPorUf', 360, function() {
            return $this->curl( $this->getUrl( 'ddd_por_uf' ) );
        });

        return $value;
    }

    public function getValidaDados( $cpf, $nome, $mae, $dataNasc ){
        return $this->curl( $this->getUrlBasic('central_api') . '/valida-dados', [
            'json' => true,
            'post' => true,
            'data' => json_encode( [ 'cpf'=>$cpf, 'nome_completo'=>$nome, 'mae'=>$mae, 'data_nascimento'=>$dataNasc ] )
        ] );
    }

    public function backEndGetSessionId( $token ){
        $retorno = $this->curl( $this->getUrlBasic( 'backend_api' ) . '/get-session-id', ['backend'=> ['token'=>$token] ] );
        return ( isset( $retorno['session_id'] ) ? $retorno['session_id'] : null );
    }

    public function backEndcheckToken( $token ){
        $ret     = $this->curl( $this->getUrlBasic( 'backend_api' ) . '/check-token/' . $token );
        $retorno = FALSE;
        if( !empty( $ret ) && array_has( $ret, 'status' ) && $ret['status'] !== FALSE ){
            unset($ret['status']);
            $retorno = $ret;
        }
        return $retorno;
    }

    public function backEndSaveRegistro( $token, $sessionID, $arrayValores ){
        $json = json_encode( $arrayValores );
        return $this->curl( $this->getUrlBasic( 'backend_api' ) . '/registra', [
            'json' => true,
            'post' => true,
            'data' => $json,
            'backend'=>[
                'token'      => $token,
                'session_id' => $sessionID
            ]
        ] );
    }

    public function backEndCreateOrder( $token, $sessionID ){
        return $this->curl( $this->getUrlBasic( 'backend_api' ) . '/create', [
            'post' => true,
            'backend'=>[
                'token'      => $token,
                'session_id' => $sessionID
            ]
        ] );
    }

    public function backEndgetInfoOrder( $token, $sessionID ){
        return $this->curl( $this->getUrlBasic( 'backend_api' ) . '/get', [
            'post' => true,
            'backend'=>[
                'token'      => $token,
                'session_id' => $sessionID
            ]
        ] );
    }

    public function backEndLogActivity( $token, $sessionID, $arrayValores ){
        $json = json_encode( $arrayValores );
        return $this->curl( $this->getUrlBasic( 'backend_api' ) . '/log-activity', [
            'json' => true,
            'post' => true,
            'data' => $json,
            'backend'=>[
                'token'      => $token,
                'session_id' => $sessionID
            ]
        ] );
    }

    public function backEndCheckStatusOrder( $token, $sessionID ){
        return $this->curl( $this->getUrlBasic( 'backend_api' ) . '/check-status-order', [
            'post' => true,
            'backend'=>[
                'token'      => $token,
                'session_id' => $sessionID
            ]
        ] );
    }

    public function getIpPermission( $ip, $site ){
        return $this->curl( $this->getUrlBasic( 'central_api' ) . '/ip-permission', [
            'json' => true,
            'post' => true,
            'data' => json_encode( [ 'ip'=>$ip, 'site'=>$site ] )
        ] );
    }

    public function getOrderCustomer( $cpf=null, $codigoPedido=null, $nascimento=null ){

        $dadosSend = [];

        if( !empty( $codigoPedido ) ){$dadosSend['codigo_pedido'] = $codigoPedido;}
        else {$dadosSend = [ 'cpf'=>$cpf, 'nascimento'=>$nascimento ];}

        return $this->curl( $this->getUrlBasic( 'get_order_customer' ), [
            'json' => true,
            'post' => true,
            'data' => json_encode( $dadosSend )
        ] );
    }

    public function getCheckSMSOnLine(){
        $ret  = $this->curl( $this->getUrlBasic( 'api_mag' ) . '/verify-sms' );
        $flag = TRUE;
        if( isset( $ret['status'] ) ) {
            $flag = $ret['status'];
        }
        return $flag;
    }

    public function sendEmail( $codTemplate, $nome, $email, $outrosDados = [] ){

        $dados['cod_template']          = $codTemplate;
        $dados['destinatario']['nome']  = $nome;
        $dados['destinatario']['email'] = $email;
        $dados['data']                  = $outrosDados;

        return $this->curl( $this->getUrlBasic( 'envia_email' ), [
            'json' => true,
            'post' => true,
            'data' => json_encode( $dados )
        ] );
    }

    public function saveLogAuditing( $arrayValores = [] ){

        return $this->curl( $this->getUrlBasic( 'central_api' ) . '/save-log-auditing', [
            'json' => true,
            'post' => true,
            'data' => json_encode( $arrayValores )
        ] );
    }

    public function sendSMS( $origem, $ddd, $telefone, $mensagem, $ip ){
        return $this->curl( $this->getUrlBasic( 'central_api' ) . '/sms', [
            'json' => true,
            'post' => true,
            'data' => json_encode( [ 'origem'=>$origem, 'ip'=>$ip, 'linha'=>$ddd . $telefone, 'mensagem'=>$mensagem ] )
        ] );
    }

    public function sendLead( $operadora, $arrayValores ){
        return $this->curl( $this->getUrlBasic( 'save_to_call_' . strtolower( $operadora ) ), [
            'json' => true,
            'post' => true,
            'data' => json_encode( $arrayValores )
        ] );
    }

    public function saveCostGoogleAd( $arrayValores = [] ){

        return $this->curl( $this->getUrl( 'api_mag' ) . "/cost-google-adwords/save", [
            'json' => true,
            'post' => true,
            'data' => json_encode( $arrayValores )
        ] );
    }

    public function getCostGoogleAd( $pass, $limit = 10 ){

        return $this->curl( $this->getUrl( 'api_mag' ) . "/cost-google-adwords/get/$pass/$limit");
    }

    public function getUrl($apiName){
        $retorno = $this->curl( config('app.url_api_center').$apiName.'/'.config('app.env') );
        return $retorno['url'];

    }

    public function urlShorten( $url ){
        $retorno = $this->curl( $this->getUrlBasic( 'url_shorten'), [
            'json' => true,
            'post' => true,
            'data' => json_encode( [ 'url' => $url ] )
        ] );
        return ( !empty( $retorno ) && !isset( $retorno['error'] ) ? $retorno['url'] : NULL );
    }

    public function getActionPromocaoLP( $arrayValores ){
        return $this->curl( $this->getUrlBasic( 'api_mag') . "/promocao/action", [
            'json' => true,
            'post' => true,
            'data' => json_encode( $arrayValores )
        ] );
    }

    public function sendDiscadorUnico( $dados ){
        return $this->curl( $this->getUrlBasic( 'discador' ) . 'register-lead', [
            'json' => true,
            'post' => true,
            'data' => json_encode( $dados )
        ] );
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
                $dados[] = 'api-token: '.$options['backend']['token'];
                if( array_has($options['backend'], 'session_id') ) {
                    $dados[] = 'session-id: '.$options['backend']['session_id'];
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
        //\Log::info( $url.' > '.$result );
        curl_close($ch);

        return $this->parseReturn( $result );
    }
}