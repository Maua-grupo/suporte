---
name: SuporteMG
description: Service desk denso e calmo — chrome ardósia, canvas teal, acento ciano de sinal.
colors:
  teal-slate: "#315565"
  teal-slate-strong: "#203f4c"
  signal-cyan: "#4ba3c7"
  signal-cyan-bright: "#7bdbff"
  slate-chrome: "#2b414b"
  slate-chrome-hi: "#36515d"
  ink: "#18313f"
  ink-soft: "#5a7280"
  canvas: "#eef3f6"
  surface: "#ffffff"
  surface-muted: "#f5f8fa"
  border: "#d7e1e8"
  border-strong: "#bccbd6"
  success: "#47a447"
  danger: "#d2322d"
  warning: "#ed9c28"
  info: "#5bc0de"
  link-blue: "#0060b3"
typography:
  headline:
    fontFamily: "Poppins, Montserrat, FreeSans, system-ui, sans-serif"
    fontSize: "1.5rem"
    fontWeight: 600
    lineHeight: 1.25
    letterSpacing: "0.01em"
  title:
    fontFamily: "Poppins, Montserrat, FreeSans, system-ui, sans-serif"
    fontSize: "1.125rem"
    fontWeight: 600
    lineHeight: 1.3
    letterSpacing: "0.01em"
  body:
    fontFamily: "Montserrat, FreeSans, system-ui, sans-serif"
    fontSize: "0.875rem"
    fontWeight: 400
    lineHeight: 1.5
    letterSpacing: "normal"
  label:
    fontFamily: "Poppins, Montserrat, sans-serif"
    fontSize: "0.6875rem"
    fontWeight: 600
    lineHeight: 1.4
    letterSpacing: "0.04em"
rounded:
  sm: "10px"
  md: "16px"
  lg: "18px"
  pill: "999px"
spacing:
  xs: "8px"
  sm: "12px"
  md: "16px"
  lg: "18px"
components:
  button-primary:
    backgroundColor: "{colors.teal-slate}"
    textColor: "{colors.surface}"
    rounded: "{rounded.sm}"
    padding: "8px 16px"
  button-primary-hover:
    backgroundColor: "{colors.teal-slate-strong}"
    textColor: "{colors.surface}"
    rounded: "{rounded.sm}"
    padding: "8px 16px"
  nav-pill:
    textColor: "{colors.surface}"
    rounded: "{rounded.pill}"
    padding: "8px 14px"
  sidebar-item:
    textColor: "{colors.surface}"
    rounded: "12px"
    padding: "10px 16px"
  card:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.ink}"
    rounded: "{rounded.md}"
    padding: "16px"
  input:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.ink}"
    rounded: "{rounded.sm}"
    padding: "8px 12px"
  badge-status:
    textColor: "{colors.surface}"
    rounded: "{rounded.pill}"
    padding: "2px 10px"
---

# Design System: SuporteMG

## 1. Overview

**Creative North Star: "The Control Room"**

O SuporteMG é uma sala de controle: o operador senta por horas e precisa ver o estado da operação — chamados, prazos, filas — de relance, sem ruído. O sistema visual é um chrome escuro em ardósia (topbar e sidebar) emoldurando um canvas de trabalho claro e calmo em teal, com um único ciano de sinal reservado para o que exige ação ou atenção. A camada `--ux-*` (o "ux refresh" em `includes/css/ux_refresh.css`) moderniza uma base **Bootstrap v4.5.2**: superfícies que flutuam com sombra difusa suave, cantos generosos e navegação em pílula, sem jamais virar vitrine.

A densidade é uma qualidade, não um defeito — cabe muita informação por tela, mas a hierarquia (peso, espaçamento e o ciano de sinal) decide o que salta primeiro. O acento nunca decora; ele marca ação, seleção ou estado. Estrutura e calma vêm do teal-ardósia; urgência e foco vêm da cor de status certa no lugar certo.

Este sistema **rejeita explicitamente** cara de site de marketing (heros, gradientes decorativos, tipografia display, animações de entrada) e padrões reinventados (scrollbars, selects e modais custom "diferentes só para ser diferente"). Familiaridade é qualidade: a base Bootstrap é preservada, não disfarçada.

**Key Characteristics:**
- Chrome ardósia escuro + canvas teal claro; um único ciano de sinal.
- Denso porém calmo: muita informação, hierarquia clara, zero decoração competindo com o dado.
- Superfícies que flutuam de leve (sombra ambiente), cantos arredondados (10–18px), nav em pílula.
- Cor de status = significado (nunca só cor: sempre com rótulo/ícone).
- Alvo WCAG 2.1 AA; `prefers-reduced-motion` respeitado.

## 2. Colors

Paleta fria e institucional: ardósia/teal carregam estrutura e calma; um ciano de sinal e os status semânticos carregam ação e urgência.

### Primary
- **Deep Teal-Slate** (#315565): cor estrutural do produto — botões primários, cabeçalhos de seção, links de ênfase, foco. Sua versão profunda **Teal-Slate Strong** (#203f4c) é o hover/pressed dos primários.
- **Slate Chrome** (#2b414b, realce #36515d): fundo do chrome — topbar e sidebar. É o "moldura" escuro que emoldura o canvas claro; o gradiente sutil (#36515d → #304955) dá vida sem virar decoração.

### Secondary
- **Signal Cyan** (#4ba3c7, brilhante #7bdbff): o **único** acento. Marca ação primária, item selecionado/ativo e indicadores de estado. Sobre o chrome escuro aparece como #7bdbff (seleção de nav); sobre o canvas claro, como #4ba3c7.

### Tertiary — Status
- **Success Green** (#47a447): chamado resolvido / dentro do prazo / confirmação.
- **Danger Red** (#d2322d): atraso, erro, exclusão, SLA estourado.
- **Warning Amber** (#ed9c28): pendência, prazo próximo, atenção.
- **Info Cyan** (#5bc0de): informação neutra, dicas.
- **Link Blue** (#0060b3): links textuais herdados da base.

### Neutral
- **Ink** (#18313f): texto de corpo e títulos sobre canvas claro. É o piso de contraste — não descer para cinza-claro "por elegância".
- **Ink Soft** (#5a7280): texto secundário/meta (timestamps, labels de apoio) — usar só quando passar 4.5:1.
- **Canvas** (#eef3f6): fundo da área de trabalho (levemente teal, nunca creme/bege).
- **Surface** (#ffffff) / **Surface Muted** (#f5f8fa): cartões, painéis, linhas alternadas de tabela.
- **Border** (#d7e1e8) / **Border Strong** (#bccbd6): divisórias e contornos de campo.

### Named Rules
**The One-Signal Rule.** Signal Cyan é a única voz de acento. Se dois elementos disputam o ciano na mesma tela, um deles está errado — status usa a cor de status, o resto usa neutro. Ação e seleção são raras o bastante para o ciano nunca poluir.

**The No-Cream Rule.** O canvas é teal-frio (#eef3f6), jamais creme/bege/parchment. "Aquecer" a interface é proibido; a identidade é fria por definição.

## 3. Typography

**Display/UI Font:** Poppins (fallback Montserrat, FreeSans, system-ui) — títulos, nav, labels.
**Body/Data Font:** Montserrat (fallback FreeSans, system-ui) — corpo, tabelas, formulários.
**Icon Font:** FontAwesome.

**Character:** dois sans geométricos próximos (Poppins + Montserrat), herdados da base. Poppins carrega o chrome e os rótulos; Montserrat/FreeSans carregam o dado denso. Como são parecidos, a hierarquia vem de **peso e tamanho**, não de contraste de família.

### Hierarchy
- **Headline** (600, 1.5rem/24px, lh 1.25): títulos de tela/seção principal.
- **Title** (600, 1.125rem/18px, lh 1.3): títulos de card/painel, cabeçalhos de bloco.
- **Body** (400, 0.875rem/14px, lh 1.5): texto e dado. Em telas embutidas (iframe) cai para 12px por densidade. Prosa longa fica em 65–75ch; tabelas podem correr mais densas.
- **Label** (600, 0.6875rem/11px, tracking 0.04em, UPPERCASE): cabeçalhos de grupo na sidebar, eyebrows funcionais de tabela/filtro.

### Named Rules
**The Weight-Not-Family Rule.** Como Poppins e Montserrat são sans geométricos parecidos, nunca conte com a diferença de família para criar hierarquia — ela quase não se lê. Hierarquia é peso (400/600) e tamanho. Não introduza um terceiro sans geométrico.

**The Caps-Sparingly Rule.** O maiúsculo tracked (label 11px) é só para cabeçalhos de grupo funcionais (sidebar, colunas). Nunca um eyebrow decorativo acima de cada seção — isso é gramática de marketing, proibido aqui.

## 4. Elevation

Elevação **ambiente leve**: cartões e painéis já "flutuam" em repouso com uma sombra difusa e suave de baixa opacidade — nunca a sombra dura e curta de app de 2014. A profundidade é calma e constante, não um evento de hover. O chrome escuro projeta uma sombra mais longa para separar-se do canvas; o conteúdo usa sombras curtas e macias.

### Shadow Vocabulary
- **Ambient SM** (`box-shadow: 0 8px 24px rgba(21,45,58,0.08)`): cards e painéis de conteúdo em repouso.
- **Ambient MD** (`box-shadow: 0 16px 32px rgba(21,45,58,0.12)`): superfície principal (iframe/área central), elementos que sobem no hover.
- **Chrome Cast** (`box-shadow: 0 12px 30px rgba(19,43,54,0.16)` / sidebar `12px 0 28px rgba(20,44,57,0.08)`): topbar e sidebar separando-se do canvas.

### Named Rules
**The Soft-Float Rule.** Toda sombra é difusa (blur ≥ 24px) e de baixa opacidade (≤ 0.16). Se a sombra parece dura, curta ou escura, está errada — a atmosfera é névoa, não recorte.

## 5. Components

### Buttons
- **Shape:** cantos arredondados moderados (10px, `{rounded.sm}`).
- **Primary:** fundo Deep Teal-Slate (#315565), texto branco, padding 8px 16px.
- **Hover / Focus:** fundo escurece para Teal-Slate Strong (#203f4c); foco visível com anel/realce (nunca só cor). Transição ~0.2s ease.
- **Secondary / Ghost:** contorno em Border Strong (#bccbd6) sobre Surface, texto Ink; usar para ações não-destrutivas secundárias.

### Chips / Badges (status)
- **Style:** pílula (999px), texto branco sobre a cor de status sólida, padding 2px 10px, tipografia label.
- **State:** um badge por estado de chamado (Aberto/Pendente/Resolvido/Atrasado). **Sempre** acompanha rótulo textual — cor nunca é o único sinal.

### Cards / Containers
- **Corner Style:** 16px (`{rounded.md}`); a superfície principal em iframe usa 18px.
- **Background:** Surface (#fff), variações em Surface Muted (#f5f8fa).
- **Shadow Strategy:** Ambient SM em repouso (ver Elevation).
- **Border:** 1px Border (#d7e1e8) quando precisa de contorno; sombra e borda não competem.
- **Internal Padding:** 16px (`{spacing.md}`).

### Inputs / Fields
- **Style:** Surface (#fff), borda Border (#d7e1e8), cantos 10px, padding 8px 12px.
- **Focus:** borda vira Signal Cyan (#4ba3c7) + leve realce; nunca remover o outline sem substituto visível.
- **Error / Disabled:** erro com borda Danger (#d2322d) e mensagem textual; disabled em Surface Muted com texto Ink Soft (mantendo contraste).

### Navigation
- **Top nav:** links em pílula (`.td-barra`, 999px) sobre o chrome; hover clareia com `rgba(255,255,255,0.14)` e sobe 1px; selecionado usa realce ciano (#7bdbff a 0.18) com inset ring.
- **Sidebar:** fundo Slate Chrome (#2b414b), itens arredondados (12px) com "tile" de ícone (10px); item ativo/hover ganha inset ring branco e o tile de ícone vira ciano (rgba(75,163,199,0.2)). Colapsa para 92px (modo pinned).

### User Chip (signature)
Pílula translúcida no topbar (`backdrop-filter: blur(6px)`, fundo `rgba(255,255,255,0.1)`, borda `rgba(255,255,255,0.14)`): identidade do usuário logado. É o **único** uso legítimo de vidro — decorativo em qualquer outro lugar é proibido.

## 6. Do's and Don'ts

### Do:
- **Do** manter o canvas teal-frio (#eef3f6) e o chrome ardósia (#2b414b); a moldura escura + trabalho claro é a assinatura.
- **Do** reservar Signal Cyan (#4ba3c7 / #7bdbff) para ação, seleção e estado ativo — a raridade é o ponto (The One-Signal Rule).
- **Do** comunicar estado de chamado com cor **e** rótulo/ícone juntos (Success #47a447, Danger #d2322d, Warning #ed9c28), nunca só cor — WCAG AA.
- **Do** manter texto de corpo em Ink (#18313f) com ≥4.5:1; Ink Soft (#5a7280) só quando passar no contraste.
- **Do** usar sombras difusas e suaves (0 8px 24px, ≤0.16 alpha) para o soft-float; borda OU sombra, não as duas competindo.
- **Do** preservar os controles Bootstrap familiares; hierarquia por peso (600) e tamanho, não por trocar de família.

### Don't:
- **Don't** dar cara de site de marketing: sem heros grandes, gradientes decorativos, tipografia display ou animações de entrada. A tela é trabalho, não vitrine.
- **Don't** reinventar padrões: nada de scrollbars, selects ou modais custom "diferentes só para ser diferente". Familiaridade é qualidade.
- **Don't** aquecer o fundo — creme/bege/parchment é proibido (The No-Cream Rule); o neutro é frio, tendendo ao teal.
- **Don't** usar o maiúsculo tracked como eyebrow decorativo acima de cada seção; só para cabeçalhos de grupo funcionais (The Caps-Sparingly Rule).
- **Don't** espalhar glassmorphism: o vidro é exclusivo do user-chip/footer; decorativo em qualquer outro lugar é proibido.
- **Don't** usar sombra dura/curta/escura (cara de 2014); se parece recorte em vez de névoa, está errada.
- **Don't** deixar dois acentos ciano disputando a mesma tela — um está errado (The One-Signal Rule).
