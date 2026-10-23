# Personalização da Tabela Periódica

## OBJETIVOS 
crie um site sobre a personalização da tabela periódica com as seguintes observações. Primeiramente, coloque uma tela inicial básica contendo informações com o nome do site e um botão para iniciar a personalização. O objetivo do site e a pessoa tem que entrar no site e ela vai poder pegar um modelo pronto da tabela periódica e a partir disso pode decorar de acordo com as preferencias.

### STACK TECNÓLOGICO
- Backend: PHP estruturado em sessões nativas
- Banco de dados: MySQL (PDO para segurança)
- Frontend: HTML5, PHP, CSS, Tailwind CSS

#### Regras de negocio (CORE) 
 Segundo, vai ter varios espaços, um para alterar a cor, adicionar adesivo, abaixar imagens e colocar na tabela, pode ter um espaço com modelos proprios, alterar o tamanho, e tudo isso sem alterar as informaçãoes da tabela periodica, e tambem pode ter uma parte para adicionar logo de empresa. Terceiro, após finalizado, o individo quando for para parte final ele vai poder ter varias opções para abaixar a tabela, pode voltar e arrumar de novo, salvar para mais tarde.
 - o modelo base da tabela deve ser vetorizado para poder caber em qualquer resolução, em hipótese alguma invente algum elemento.
 - https://www.tabelaperiodica.org/wp-content/uploads/2025/03/Tabela-completa-5-algarismos-sem-intervalo-v12.pdf . A parte deste pdf é a base e nada dele pode ser alterado, a não ser o formato dos quadrinhos em volta dos elementos 
 - Ao entrar tem que fazer login
 - <img width="1535" height="1024" alt="image" src="https://github.com/user-attachments/assets/2b128b04-e2ee-4649-9250-c49f25d71df7" /> . Essa e a base do site sendo baseado nesse estilo


##### Regras globais
- Use sempre o mesmo PDO para conexão e queries no MySQL para evitar SQL Injections
- Mantenha o código limpo e comente apenas lógicas complexas.
- Separe os arquivos de forma lógica: um arquivo para conexão com a base (bd.php) e scripts de backend isolados em views em HTML5/PHP
- Estilize as telas em tailwind de forma responsiva priorizando o MobileFirst.

