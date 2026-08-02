# Food in Time — reserva de refeições

Projeto acadêmico de reserva antecipada de refeições em restaurantes. Esta versão mantém a proposta original, mas corrige o fluxo principal para que os pedidos sejam persistidos no MySQL e possam ser consultados pelo restaurante.

## Funcionalidades

- autenticação com senha protegida por `password_hash`
- sessão com cookie `HttpOnly` e `SameSite=Lax`
- proteção CSRF nos formulários e endpoints
- seleção de restaurante e múltiplos pratos
- validação dos pratos no servidor
- gravação transacional de pedidos e itens
- painel de pedidos para administradores e restaurantes
- consultas preparadas com PDO
- credenciais locais fora do controle de versão
- validação de sintaxe PHP no GitHub Actions

## Requisitos

- PHP 8.1 ou superior com a extensão PDO MySQL
- MySQL 8 ou MariaDB compatível
- Apache, Nginx ou servidor embutido do PHP

## Instalação local

1. Crie um banco vazio chamado `alimentacao`.
2. Importe o arquivo `alimentacao.sql`.
3. Copie `config.example.php` para `config.local.php`.
4. Preencha `config.local.php` com um usuário MySQL restrito ao banco da aplicação.
5. Inicie o servidor na raiz do projeto.
6. Acesse `index.php`.

Exemplo usando o servidor embutido do PHP:

```bash
cp config.example.php config.local.php
php -S 127.0.0.1:8080
```

Depois acesse `http://127.0.0.1:8080`.

Também é possível configurar `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER` e `DB_PASSWORD` como variáveis de ambiente no lugar de `config.local.php`.

## Usuário demonstrativo

O arquivo SQL cria um usuário apenas para demonstração:

- usuário: `Rafael`
- senha: `TroqueEstaSenha123!`

Troque ou remova esse usuário antes de disponibilizar a aplicação em qualquer servidor acessível por terceiros.

## Fluxo do pedido

1. O usuário entra na aplicação.
2. Seleciona o restaurante.
3. Seleciona de 1 a 20 pratos.
4. Revisa horário, modalidade e observações.
5. A aplicação valida novamente restaurante e pratos.
6. Pedido e itens são gravados em uma única transação.
7. Usuários de restaurante ou administradores acompanham os pedidos em `processar_pedido.php`.

## Estrutura de dados

- `clientes`: autenticação e perfil de acesso
- `restaurantes`: estabelecimentos disponíveis
- `pratos`: pratos vinculados a um restaurante
- `pedidos`: cabeçalho, status e informações do pedido
- `pedido_itens`: pratos pertencentes ao pedido

## Segurança

Este projeto é educacional. Antes de uma implantação real, ainda devem ser acrescentados HTTPS obrigatório, política de senhas, recuperação de conta, auditoria, rate limiting compartilhado e testes de segurança.

## Histórico

A primeira versão foi criada em 2023 como Projeto Integrador do SENAC. Sua proposta, limitações e evolução estão registradas em [docs/historico-2023.md](docs/historico-2023.md).

O código original permanece no repositório [Senac_PI_2023](https://github.com/rafajob/Senac_PI_2023), preservando colaboradores e commits. O fluxo atual foi modernizado para corrigir inconsistências de banco, retirar credenciais do código, reduzir riscos de XSS e substituir o armazenamento temporário em sessão por persistência real.
