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


### Resumo do DDD utilizado no projeto ###
**Administradores e usuários são parecidos, porém `NÃO SÃO IGUAIS`**
 - Logo, não existem motivos para estarem na mesma entidade. Isso cria uma maior complexidade inicial, porém a longo prazo permite que os métodos não precisem de verificações constantes ou que as regras de negócio se tornem mais complexas devido a essas verificações.

**Você não deve utilizar uma entidade baseada em uma ORM**
 - Ao utilizar entidades baseadas em ORM, além da perda de desempenho já comprovada (https://www.youtube.com/watch?v=3TJfR1Ta4GU)
 - Você não conseguirá desacoplar esse código no futuro, caso queira trocar para um banco de dados diferente(seja ele sql ou noSql) sua entidade deveria permitir esse tipo de troca. já que a responsabilidade de salvar/modificar/deletar qualquer dado é responsabilidade do repositório.

**Suas entidades não devem ser pobres/anemicas!!!**
 - As entidades não podem estar vazias, as regras de negócio devem estar dentro delas, não de outras entidades.

**Alguns códigos irão se repetir porém isso será necessário**
 - Um usuário e um administrador tem as mesmas opções para fazer update em certos dados, porém, a entidade usuário faz um tipo de update, e a entidade do administrador faz outra. nesse exemplo(e em outros) o DDD ultrapassa o SOLID para manter o sistema com uma forma mais dinâmica e organizada.
 - A longo prazo isso irá economizar validações desnecessárias, tais como verificar se um usuário é um administrador antes de fazer alguma modificação, ou se o nível de acesso está correto.

**O sistema não pode ser obcecado por tipos primitivos**


**Entrando no TDD**
 - Seu sistema deve poder ser utilizado via CLI ou qualquer outro tipo de saida/entrada de dados e ser completamente da interface utilizada.

### Entenda como funciona o TDD ###
 - Ao desenvolver utilizando TDD algumas regras sempre devem ser levadas em consideração:
 - O sistema precisa ser completamente testável(seja via postman com uma API para testes de endpoint) sejam menores partes do código dos value objects.
 - 
