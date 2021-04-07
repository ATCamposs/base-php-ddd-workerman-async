### Motivos para usar workerman
Além de mais rápido que o swoole, não necessita de libs externas, porém o ponto negativo é não possuir corrotinas, sendo necessária fazer uma chamada para outra thread(no repositório tem um exemplo usando o envio de emails) para fazer um serviço de forma assincrona.

### Endpoints disponíveis
**/register (post)**
 - necessário `user_name`, `email`, `password` e `confirm_password`.
 - caso o usuário seja registrado corretamente, uma mensagem com status 201 deve retornar.
 - o usuário criado vem com padrão inativo e recebe um activation_hash para ativação.

**/activate?activation_code=xxxxxxxxxxx (get)**
 - o `activation_hash` de um usuário.

**/login (post)**
 - necessário `email` e `password` de um usuário.
 - o retorno é um jwt token valido pelo tempo determinado no .env.

**/users/logout (post)**
 - apaga a sessão atual do usuário.

**/users/delete (delete)**
 - deleta o usuário (desde que seja o usuário da sessao ativa no momento e o token esteja correto).

**/users/update/user_name, /users/update/email, /users/update/password**
 - são auto explicativos, fazem update dos dados do usuário, cada um com seu input específico.

**/admin/update/user_name, /admin/update/email, /admin/update/activate, /admin/update/access_level**
 - praticamente os mesmos endpoints dos usuários, porém pode fazer update de dados de outros usuários.
 - pode ativar outros usuários.
 - pode alterar o nivel de acesso de outros usuários(desde que abaixo do próprio nivel de acesso).

## Método de instalação ##
**requisitos necessários**
 - PHP 7.4
 - composer
 - redis
 - mariadb/mysql
 - **Configurar o .env**

Para instalar o sistema rodar o comando `composer install`.

Para adicionar as tabelas ao banco de dados rodar o comando `vendor/bin/phinx migrate`.

Para executar os testes é só rodar o comando `vendor/bin/phpunit` na pasta raiz.

**Para verificar todas as mensagens da API traduzidas deixar o `APP_DEBUG=false` no .env**

### Resumo do escalonamento horizontal ###
Ao usar um sistema assincrono com o PHP temos uma quantidade de requisições que chega mais próxima a linguagens compiladas como o Go/rust. Porém em frameworks fullStack esse aumento de velocidade é grandemente reduzido e se torna inviável.

exemplos:
Ao utilizar um microframework temos a opção de fazer o acesso a session estar presente no redis. podendo a longo prazo separar os servidores, redis e sql completamente.

Um serviço que funciona em uma thread a parte é o de envio de emails que está aberto via websocket e também pode ser desacoplado sem grandes problemas do serviço principal.


### Resumo do DDD utilizado no projeto ###
**Administradores e usuários são parecidos, porém `NÃO SÃO IGUAIS`**
 - Logo, não existem motivos para estarem na mesma entidade. Isso cria uma maior complexidade inicial, porém a longo prazo permite que os métodos não precisem de verificações constantes ou que as regras de negócio se tornem mais complexas devido a essas verificações.

**Você não deve utilizar uma entidade baseada em uma ORM**
 - Ao utilizar entidades baseadas em ORM, além da perda de desempenho já comprovada (https://www.youtube.com/watch?v=3TJfR1Ta4GU).
 - Você não conseguirá desacoplar esse código no futuro, caso queira trocar para um banco de dados diferente(seja ele sql ou noSql) sua entidade deveria permitir esse tipo de troca. já que a responsabilidade de salvar/modificar/deletar qualquer dado é responsabilidade do repositório.
 - Você não poderá desacoplar suas regras de negócio do framework que utiliza, deixando todo o projeto preso em uma "facilidade" inicial.

**Suas entidades não devem ser pobres/anemicas!!!**
 - As entidades não podem estar vazias, as regras de negócio devem estar dentro delas, não de outras entidades.
 - As entidades não podem simplesmente ser getters/setters para regras contidas em outros lugares.

**Alguns códigos irão se repetir porém isso será necessário**
 - Um usuário e um administrador tem as mesmas opções para fazer update em certos dados, porém, a entidade usuário faz um tipo de update em seu escopo, e a entidade do administrador a faz em um escopo diferente. Nesse exemplo(e em outros) o DDD ultrapassa o SOLID para manter o sistema com uma forma mais dinâmica e organizada.
 - A longo prazo isso irá economizar validações desnecessárias, tais como verificar se um usuário é um administrador antes de fazer alguma modificação, ou se o nível de acesso está correto.

**O desenvolvedor não pode ser obcecado por tipos primitivos**
 - Um email não é uma string, uma senha ou um nome de usuário também não são simples strings. esses objetos de valor deveriam estar criados com erros(exceptions) e validações próprias para cada tipo de dado.


**Entrando no TDD**
 - Seu sistema deve poder ser utilizado via CLI ou qualquer outro tipo de saida/entrada de dados e ser completamente independente da interface utilizada. (a interface web é apenas um dos possíveis métodos de utilização).

### Entenda como funciona o TDD ###
**Ao desenvolver utilizando TDD algumas regras sempre devem ser levadas em consideração:**
 - O sistema precisa ser completamente testável(seja via postman com uma API para testes de endpoint) sejam menores partes do código e a validação dos value objects.
 - Você deve desenvolver a nova implementação criando primeiro os testes para a mesma, assim pensará sempre nas regras envolvidas na nova implementação e estará menos suscetível a falhas.


### Entenda o object calisthenics: ###
 - Only One Level Of Indentation Per Method (Apenas um nível de identação por método)
   - Você deve manter seu código simples, se ele precisa de mais um nível de identação, provavelmente você poderia por uma parte desse código dentro de um método diferente(dificilmente métodos com mais de um nivel de identação(no máximo 2) seguem as regras do SOLID).
 - Don’t Use The ELSE Keyword (não use else)
   - Utilizar else dentro do código geralmente está tratando 2 tipos diferentes de resultados, isso não deveria ocorrer ao seguir o principio de "falhar primeiro"(https://martinfowler.com/ieeeSoftware/failFast.pdf)
 - Wrap All Primitives And Strings(envolva todos os tipos primitivos e strings)
   - Essa regra já é utilizada dentro do DDD, você deve encapsular valores dentro de objetos de valor dentro do seu domínio, um exemplo disso são cpf, email, senha, todos são valores específicos que enriquecem seus domínios e não devem ser tratados como tipos primitívos.
 - First Class Collections (coleções de primeira classe)
   - Objetos de coleção(iteráveis) devem ter uma classe iterável a parte, e não devem ser tratadas como arrays/objetos avulsos.
 - One Dot Per Line
 - Don’t Abbreviate
 - Keep All Entities Small
 - No Classes With More Than Two Instance Variables
 - No Getters/Setters/Properties
