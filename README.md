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

**configurações importantes do .env**

SERVER_PROCESS_COUNT= numero de processos(geralmente o dobro de nucleos do processador)

MAIL_PROCESS_COUNT= numero de processos para o servidor de email, pode ser somente 1, caso haja muita carga pode dividir a quantidade de processos com o server_process_count


Para instalar o sistema rodar o comando `composer install`.

Para adicionar as tabelas ao banco de dados rodar o comando `vendor/bin/phinx migrate`.

Para executar os testes é só rodar o comando `vendor/bin/phpunit` na pasta raiz.

**Para verificar todas as mensagens da API traduzidas deixar o `APP_DEBUG=false` no .env**

**Como iniciar o projeto**

`php start.php start`

o código é atualizado automaticamente pelo reloader, logo mesmo ficando armazenado na memória você não precisa parar o processo e iniciar novamente a cada mudança no código.

para parar o processo você pode apertar as teclas crtl+c no terminal ou enviar o comando `php start.php stop` em outro terminal.


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

## Entenda a raiz de diretórios utilizando DDD ##
 - Todo projeto a nivel de domínio está dentro da pasta `src`
 - Regras complexas devem estar em pastas separadas(no caso `users`), pense nela como tendo toda complexidade em volta dos usuários

**Pasta src/Users/Application**
 - O arquivo presente aqui (userServices.php) diz respeito a diferentes tipos de ações que o usuário pode tomar dentro do sistema ex:
   - Login
   - Register
   - Activate
   - Delete
 - Todas elas são ações que a entidade pode tomar(entidade usuário) que sejam como "ações do usuário". porém não alteram os valores da própria entidade, esses métodos devem ficar dentro da mesma.

**Pasta src/Users/Domain**
 - Os arquivos na pasta de domínio são divididos entre:
  - Uma pasta para as exceptions presentes tanto nas entidades quanto nos value objects
  - Uma pasta para os value objects(evitando o uso constante de tipos primitivos)
  - As regras utilizadas como no banco de dados devem estar presentes aqui na forma de uma interface, que deve ser implementada para TODOS os tipos de bancos de dados que venham a ser utilizados no sistema, não fazendo necessária a alteração na entidade de usuários(por exemplo)
  - O arquivo handler(e outros que venham a aparecer) contem os métodos que a entidade usuário pode usar para "se representar" dentro do sistema.

**Pasta src/Users/Infrastructure**
 - Aqui ficam todas as partes implementadas pelas interfaces dentro do sistema.
 - No caso, para o repository(alterações no banco de dados) usamos o Illuminate(por isso RepositoryIlluminate)
 - O JWT tem sua interface levemente mais complexa, por isso tem uma pasta separada para configurações.
 - O PHPMailer(também implementado no domínio) utilizado no sistema, também poderia ser substituido por qualquer outro mail sender que pudesse ser implementado na mesma interface.

**Pasta src/Users/Presentation**
 - Os templates de email(de emails para os usuários) ficam aqui dentro também, já que pertencem exclusivamente a esse domínio.
 - O authentication e administration são uma forma de interface do sistema(no caso acesso via http como uma API), e sua unica função é direcionar os dados para o domínio que, pode ser acessado via CLI também.

**Por se tratar de um assunto extremamente complexo e longo, os domínios aplicados aqui podem servir para consulta, mas não como guia para a sua aplicação, a única intenção desse repositório é despertar sua curiosidade sobre a importância do DDD**

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
 - Wrap All Primitives And Strings (envolva todos os tipos primitivos e strings)
   - Essa regra já é utilizada dentro do DDD, você deve encapsular valores dentro de objetos de valor dentro do seu domínio, um exemplo disso são cpf, email, senha, todos são valores específicos que enriquecem seus domínios e não devem ser tratados como tipos primitívos.
 - First Class Collections (coleções de primeira classe)
   - Objetos de coleção(iteráveis) devem ter uma classe iterável a parte, e não devem ser tratadas como arrays/objetos avulsos.
 - One Dot Per Line (um ponto por linha)
   - Essa regra diz que você não deve encadear métodos, a não ser que esteja usando o padrão Method Chaining Pattern.
 - Don’t Abbreviate (não abrevie)
   - Não abrevie nomes de métodos/classes ou objetos, novamente: se o nome de um método(por exemplo) é muito grande, provavelmente ele não está seguindo o SOLID e está fazendo mais de uma função dentro da classe.
 - Keep All Entities Small (mantenha todas as entidades pequenas)
   - Outra regra que segue os princípios do DDD, se uma classe tem muitos métodos, provavelmente ela poderia ser dividida em outras classes menores e mais coesas com seu próprios contextos.
 - No Classes With More Than Two Instance Variables (sem classes com mais de duas variáveis de instância)
   - Mais uma regra que segue os princípios do SOLID e do DDD, se suas classes tem muitas variáveis de instância, provavelmente ela está fazendo mais do que deveria. aumentando a complexidade do código e dificultando a manutenção.
 - No Getters/Setters/Properties (sem getters/setters)
   - Você não deve alterar as propriedades da sua classe simplesmente setando outros valores a ela sem motivo, logo as propriedades de set devem ser pensadas para continuarem dando coerencia e funcionalidade a classe, sem ferir os princípios do SOLID.
   - Basicamente: você não deve poder alterar os valores de sua classe sem motivo, apesar de "fácil" esse é um dos principais motivos das entidades estarem anêmicas a longo prazo.

### Este projeto é um exemplo e estará em constante crescimento. a qualquer dúvida/sugestão/melhoria, sinta-se livre para abrir uma issue/PR ###
