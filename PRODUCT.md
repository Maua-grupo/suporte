# Product

<!--
  RASCUNHO gerado pelo Claude a partir da leitura do código (OcoMon 5.x / SuporteMG).
  Os campos ESTRATÉGICOS (Brand Personality, Anti-references, Design Principles,
  Accessibility) são HIPÓTESES — confirme/ajuste rodando `/impeccable init`,
  que faz a entrevista curta e reescreve este arquivo com suas respostas.
  Register/Users/Product Purpose foram inferidos do código com alta confiança.
-->

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

<!-- HIPÓTESE — confirmar no /impeccable init -->

Confiável, eficiente e discreta. Três palavras: **confiável, ágil, sóbria**. A interface é uma ferramenta de trabalho: deve transmitir controle e clareza, não personalidade de marca. O objetivo emocional é confiança e foco — o operador precisa sentir que a ferramenta some dentro da tarefa.

## Anti-references

<!-- HIPÓTESE — confirmar no /impeccable init -->

- Não deve parecer um site de marketing/landing page: nada de heros grandes, gradientes decorativos, animações de entrada ou tipografia display.
- Não deve parecer um "admin template" genérico e barulhento (cards aninhados, mil cores de status saturadas, ícones decorativos sem função).
- Nada de reinventar affordances padrão (scrollbars custom, modais para tudo, selects estranhos). Familiaridade é qualidade aqui.

## Design Principles

<!-- HIPÓTESE — confirmar no /impeccable init -->

1. **A ferramenta some na tarefa.** Densidade e velocidade acima de espetáculo visual; o operador fica na fila, não admirando a UI.
2. **Prazo sempre visível.** SLA e estado do chamado são a informação mais importante da tela — hierarquia e cor servem a isso.
3. **Consistência sobre surpresa.** Mesmo botão, mesma cor de status, mesmo vocabulário de formulário em todas as telas.
4. **Estado explícito.** Todo componente interativo tem hover/focus/active/disabled/loading/erro; toda lista tem estado vazio que ensina, não "nada aqui".
5. **Cor com significado.** Acento e cores de status comunicam estado (aberto, atrasado, resolvido), nunca decoração.

## Accessibility & Inclusion

<!-- HIPÓTESE — confirmar no /impeccable init -->

- Alvo **WCAG 2.1 AA**: contraste mínimo em texto e nas cores de status; foco de teclado visível em toda a fila e formulários.
- Operação por teclado no fluxo de chamados (navegar lista, abrir, salvar) sem depender do mouse.
- Não comunicar estado só por cor (atrasado/resolvido também por rótulo/ícone) — daltonismo.
- Respeitar `prefers-reduced-motion`; movimento só para feedback de estado.
