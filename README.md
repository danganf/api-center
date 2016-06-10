# api-center

# Configuração

1 - no arquivo config/app.php, coloque a seguinte linha:
'url_api_center' => env('URL_API_CENTER', URL_API_CENTER),#terminada com '/'

2 - no mesmo arquivo, certifique-se que APP_ENV esta setado como 'production'

3 - no arquivo .env, cria as seguintes linhas:
APP_ENV=local
URL_API_CENTER=ColoqueAquiAUrlLocal