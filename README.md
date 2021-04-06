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


