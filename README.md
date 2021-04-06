### Motivos para usar workerman
Além de mais rápido que o swoole, não necessita de libs externas, porém o ponto negativo é não possuir corrotinas

### Endpoints disponíveis
**/register (post)**
 - necessário `user_name`, `email`, `password` e `confirm_password`
 - caso o usuário seja registrado corretamente, uma mensagem com status 201 deve retornar
 - o usuário criado vem com padrão inativo e recebe um activation_hash para ativação

**/activate?activation_code=xxxxxxxxxxx (get)**
 - o `activation_hash` de um usuário

**/login (post)**
 - necessário `email` e `password` de um usuário
 - o retorno é um jwt token valido pelo tempo determinado no .env

**/users/logout (post)**
 - apaga a sessão atual do usuário

**/users/delete (delete)**
 - deleta o usuário (desde que seja o usuário da sessao ativa no momento e o token esteja correto)

**/users/delete (delete)**
 - deleta o usuário (desde que seja o usuário da sessao ativa no momento e o token esteja correto)

**/users/update/user_name, /users/update/email, /users/update/password**
 - são auto explicativos, fazem update dos dados do usuário, cada um com seu input específico.

**/admin/update/user_name, /admin/update/email, /admin/update/activate, /admin/update/access_level**
 - praticamente os mesmos endpoints dos usuários, porém pode fazer update de dados de outros usuários
 - pode ativar outros usuários
 - pode alterar o nivel de acesso de outros usuários(desde que abaixo do próprio nivel de acesso)

## Método de instalação ##
**requisitos necessários**
 - PHP 7.4
 - composer
 - redis
 - mariadb/mysql
 - **Configurar o .env**

Para instalar o sistema rodar o comando `composer install`.

Para adicionar as tabelas ao banco de dados rodar o comando `vendor/bin/phinx migrate`

Para executar os testes é só rodar o comando `vendor/bin/phpunit` na pasta raiz.

**Para verificar todas as mensagens da API traduzidas deixar o `APP_DEBUG=false` no .env**

### Resumo do escalonamento horizontal ###
Ao usar um sistema assincrono com o PHP temos uma quantidade de requisições que chega mais próxima a linguagens como o Go. Porém em frameworks fullStack esse aumento de velocidade é grandemente reduzido e se torna inviável.
Ao utilizar um microframework temos a opção de fazer o acesso a session estar presente no redis. podendo a longo prazo separar os servidores, redis e sql completamente.
Um serviço que funciona em uma thread a parte é o de envio de emails que está aberto via websocket e também pode ser desacoplado sem grandes problemas do serviço principal.

