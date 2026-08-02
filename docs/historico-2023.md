# Histórico do projeto — versão 2023

Este documento registra a primeira etapa do **Food in Time**, desenvolvida em 2023 como Projeto Integrador do SENAC.

O código original continua disponível em [rafajob/Senac_PI_2023](https://github.com/rafajob/Senac_PI_2023). Ele não foi copiado para esta pasta porque o repositório separado preserva os autores, os commits e a evolução real do trabalho, além de evitar que código antigo e inseguro seja confundido com a aplicação atual.

## Objetivo da primeira versão

A proposta inicial era reduzir o tempo de espera em restaurantes à la carte permitindo que o cliente:

1. entrasse na aplicação;
2. escolhesse um restaurante;
3. selecionasse um prato;
4. informasse horário, modalidade e observações;
5. enviasse o pedido para um painel do restaurante.

A versão utilizava PHP, HTML, JavaScript, PDO e MySQL. Ela foi importante para validar a ideia e praticar autenticação, formulários, consultas SQL e comunicação assíncrona com AJAX.

## O que a versão de 2023 demonstrava

- autenticação básica por usuário e senha;
- listagem de restaurantes;
- pratos filtrados por restaurante;
- formulário de confirmação;
- protótipo de painel para acompanhar pedidos;
- banco relacional com clientes, restaurantes e pratos;
- interface adaptada para telas menores.

## Limitações identificadas

A primeira implementação cumpria o papel de protótipo acadêmico, mas possuía limitações que impediam uso real:

- pedidos eram armazenados em `$_SESSION`, ficando presos ao navegador do cliente;
- o navegador do restaurante não compartilhava a mesma sessão;
- o banco não possuía tabelas de pedidos e itens;
- credenciais MySQL estavam dentro do código;
- senhas eram armazenadas sem hash;
- algumas entradas eram exibidas sem escape consistente;
- as rotas não possuíam proteção CSRF;
- a autenticação não diferenciava cliente, restaurante e administrador;
- não havia testes ou integração contínua.

## Evolução na versão de 2024

| Área | Versão 2023 | Versão 2024 modernizada |
|---|---|---|
| Pedidos | Sessão do navegador | MySQL com `pedidos` e `pedido_itens` |
| Gravação | Dados temporários | Transação entre pedido e itens |
| Senhas | Valor simples | `password_hash` e `password_verify` |
| Banco | Credenciais no código | Configuração local ou variáveis de ambiente |
| Segurança | Validação parcial | Autenticação, perfis, CSRF e escape de saída |
| Pratos | Nome enviado pelo cliente | IDs validados novamente no servidor |
| Painel | Sessão isolada | Consulta de pedidos persistidos |
| Qualidade | Sem automação | GitHub Actions com validação de sintaxe PHP |

## Decisões preservadas

Algumas ideias da primeira versão continuam presentes:

- fluxo simples em etapas;
- uso de PHP sem framework para manter o projeto didático;
- consultas preparadas com PDO;
- foco em restaurantes físicos, não em delivery;
- interface leve e executável em ambiente local.

## Linha do tempo

- **2023:** criação do MVP acadêmico e validação da ideia;
- **2024:** expansão da seleção de pratos e da interface;
- **2026:** revisão do código, correção da persistência, segurança básica, documentação e CI.

## Repositório original

A implementação completa de 2023, incluindo README, vídeo e histórico de commits, permanece em:

[github.com/rafajob/Senac_PI_2023](https://github.com/rafajob/Senac_PI_2023)
