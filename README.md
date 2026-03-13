O EventFlow Web foi construído utilizando a arquitetura MVC (Model-View-Controller), integrando o Laravel 11 no backend e o Vue 3 no frontend. As principais escolhas técnicas basearam-se em:

Padrão MVC e Separação de Responsabilidades: O projeto utiliza o Laravel para gerenciar a lógica de negócio e persistência de dados (Models), o controle de fluxo e autenticação (Controllers) e o Vue 3 para a camada de apresentação (View). Essa separação facilita a manutenção e permite que a interface seja reativa sem sobrecarregar o servidor.

Comunicação via API (JSON): A interação entre frontend e backend ocorre de forma assíncrona através de requisições HTTP (fetch), utilizando o formato JSON para o intercâmbio de dados. Isso garante que a aplicação se comporte como uma SPA (Single Page Application), oferecendo uma experiência de usuário mais fluida e rápida.

Segurança e Autenticação: Foi implementado o Laravel Sanctum para a autenticação baseada em tokens. Isso permite proteger as rotas de criação, edição e exclusão de eventos, garantindo que apenas usuários autenticados e proprietários dos registros possam realizar alterações no banco de dados PostgreSQL.

Experiência do Usuário (UX): Foram aplicadas validações de dados tanto no cliente (JavaScript) quanto no servidor (Laravel Requests), prevenindo inconsistências como o registro de eventos em datas retroativas e garantindo a integridade das informações.