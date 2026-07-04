# Product

## Register

product

## Users

Equipe interna de TI e suporte do Mauá Group operando o service desk no dia a dia:

- **Operadores/técnicos de suporte** — vivem na fila de chamados: abrem, triam, atendem, transferem entre áreas, registram ocorrências e acompanham SLAs. Contexto de uso: muitas horas por dia, alto volume, foco em velocidade e em não perder prazos.
- **Administradores/gestores** — configuram áreas, categorias, SLAs, usuários e níveis de acesso; acompanham indicadores e relatórios de atendimento e de inventário (invmon).
- **Solicitantes (nível "abertura")** — usuários finais que apenas abrem chamados e acompanham o andamento; contato pontual, esperam clareza e rapidez.

Uso primário em desktop, dentro do trabalho, em sessões longas. Idioma principal: português (pt-BR).

## Product Purpose

SuporteMG é a instância do **OcoMon** (sistema livre de gestão de chamados/service desk e inventário, PHP + MySQL, GPLv3) usada pelo Mauá Group para centralizar o atendimento de TI.

O que faz: registro e ciclo de vida de chamados/ocorrências, categorização por área, controle e contagem de SLA, abertura de chamados por e-mail, base de conhecimento/inventário (invmon), níveis de acesso por perfil, relatórios e integração via API.

Por que existe: dar previsibilidade e rastreabilidade ao atendimento — nada cai no esquecimento, prazos são visíveis e o histórico fica auditável. Sucesso = operador resolve mais chamados dentro do SLA com menos atrito, e o gestor enxerga o estado da operação de relance.

## Brand Personality

**Ágil e densa.** Três palavras: **ágil, densa, eficiente.** É uma ferramenta de produção: velocidade e densidade de informação vêm antes de qualquer espetáculo visual. Muita informação por tela é uma qualidade, não um problema — desde que a hierarquia deixe claro o que importa. O objetivo emocional é confiança e foco: a interface some dentro da tarefa e o operador confia que nada vai passar batido. Densidade sim; ruído não.

## Anti-references

- **Cara de site de marketing.** Nada de heros grandes, gradientes decorativos, tipografia display, "eyebrows"/kickers acima de cada seção ou animações de entrada. A tela é trabalho, não vitrine.
- **Padrões reinventados.** Nada de scrollbars, selects, modais ou controles custom "diferentes só para ser diferente". Manter affordances familiares (a base Bootstrap já em uso) — previsibilidade vale mais que surpresa. Familiaridade é qualidade aqui.

## Design Principles

1. **A ferramenta some na tarefa.** Densidade e velocidade acima de espetáculo visual; o operador fica na fila resolvendo, não admirando a UI.
2. **Densidade sem ruído.** Cabe muita informação por tela, mas hierarquia (peso, espaçamento, cor) decide o que salta primeiro. Decoração nunca compete com o dado.
3. **Prazo sempre visível.** SLA e estado do chamado são a informação mais importante da tela; hierarquia e cor servem a isso antes de qualquer outra coisa.
4. **Familiaridade é qualidade.** Usar padrões e controles conhecidos; jamais reinventar affordances padrão. Mesmo botão, mesma cor de status, mesmo vocabulário de formulário em todas as telas.
5. **Estado explícito.** Todo componente interativo tem hover/focus/active/disabled/loading/erro; toda lista tem estado vazio que ensina, não "nada aqui".

## Accessibility & Inclusion

- Alvo **WCAG 2.1 AA**: contraste mínimo 4.5:1 em texto de corpo e nas cores de status; foco de teclado visível em toda a fila e nos formulários.
- Não comunicar estado apenas por cor (atrasado/resolvido/pendente também por rótulo ou ícone) — daltonismo.
- Operação por teclado no fluxo de chamados sempre que possível (navegar lista, abrir, salvar) sem depender do mouse.
- Respeitar `prefers-reduced-motion`; movimento só para feedback de estado, nunca decorativo.
