<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            // ========================================
            // PITCH DECK
            // ========================================
            [
                'name' => 'Pitch Deck Startup',
                'slug' => 'pitch-deck-startup',
                'description' => 'Template completo para apresentar sua startup a investidores. Inclui slides de problema, solução, mercado, modelo de negócio e mais.',
                'category' => 'pitch',
                'icon' => 'rocket',
                'is_premium' => false,
                'slides' => [
                    [
                        'title' => 'Capa',
                        'content' => "# [Nome da Startup]\n\n## Transformando [Indústria] com [Solução]\n\n---\n\n**Rodada:** Seed\n**Investimento:** R$ X milhões\n\n*Apresentação para Investidores - 2024*",
                    ],
                    [
                        'title' => 'O Problema',
                        'content' => "# O Problema\n\n## Descrição do problema que você resolve\n\n- 🔴 **Dor 1:** Descreva a primeira dor do cliente\n- 🔴 **Dor 2:** Descreva a segunda dor\n- 🔴 **Dor 3:** Descreva a terceira dor\n\n> \"Citação de um cliente real sobre o problema\"\n\n**Impacto:** R$ X bilhões perdidos anualmente",
                    ],
                    [
                        'title' => 'A Solução',
                        'content' => "# Nossa Solução\n\n## [Nome do Produto/Serviço]\n\n✅ **Benefício 1:** Como resolvemos a dor 1\n\n✅ **Benefício 2:** Como resolvemos a dor 2\n\n✅ **Benefício 3:** Como resolvemos a dor 3\n\n### Diferencial Competitivo\n\nNossa tecnologia única permite...",
                    ],
                    [
                        'title' => 'Mercado',
                        'content' => "# Oportunidade de Mercado\n\n## TAM / SAM / SOM\n\n| Mercado | Valor |\n|---------|-------|\n| **TAM** (Total) | R$ XX bilhões |\n| **SAM** (Endereçável) | R$ X bilhões |\n| **SOM** (Alcançável) | R$ XXX milhões |\n\n### Crescimento\n\n📈 **CAGR:** 25% ao ano\n\n🌍 **Tendência:** Descrição da tendência de mercado",
                    ],
                    [
                        'title' => 'Modelo de Negócio',
                        'content' => "# Modelo de Negócio\n\n## Como geramos receita\n\n### Fontes de Receita\n\n1. **SaaS Mensal:** R$ XX/mês por usuário\n2. **Enterprise:** Contratos anuais\n3. **Marketplace:** X% por transação\n\n### Métricas\n\n| Métrica | Valor |\n|---------|-------|\n| LTV | R$ X.XXX |\n| CAC | R$ XXX |\n| LTV/CAC | Xx |",
                    ],
                    [
                        'title' => 'Tração',
                        'content' => "# Tração\n\n## Nossos números até agora\n\n### Crescimento\n\n📊 **MRR:** R$ XXX mil\n📈 **Crescimento mensal:** XX%\n👥 **Clientes ativos:** XXX\n\n### Marcos Importantes\n\n- ✅ MVP lançado (Mês/Ano)\n- ✅ Primeiro cliente pagante\n- ✅ Product-Market Fit validado\n- 🎯 Meta: X clientes até fim do ano",
                    ],
                    [
                        'title' => 'Time',
                        'content' => "# Time Fundador\n\n## Somos as pessoas certas para resolver esse problema\n\n### Fundadores\n\n👤 **[Nome] - CEO**\nEx-[Empresa]. XX anos em [área].\n\n👤 **[Nome] - CTO**\nEx-[Empresa]. Especialista em [tecnologia].\n\n👤 **[Nome] - COO**\nEx-[Empresa]. MBA por [Universidade].\n\n### Advisors\n\n- [Nome] - [Cargo/Empresa]\n- [Nome] - [Cargo/Empresa]",
                    ],
                    [
                        'title' => 'Ask',
                        'content' => "# O Que Buscamos\n\n## Investimento Seed de R$ X milhões\n\n### Uso dos Recursos\n\n| Área | % | Valor |\n|------|---|-------|\n| Produto | 40% | R$ XXX mil |\n| Marketing | 30% | R$ XXX mil |\n| Time | 20% | R$ XXX mil |\n| Operações | 10% | R$ XXX mil |\n\n### Próximos Passos\n\n1. Escalar time de vendas\n2. Lançar versão 2.0\n3. Expandir para novos mercados\n\n📧 **Contato:** email@startup.com",
                    ],
                ],
            ],

            // ========================================
            // AULA / WORKSHOP
            // ========================================
            [
                'name' => 'Aula Interativa',
                'slug' => 'aula-interativa',
                'description' => 'Template para aulas e cursos com estrutura pedagógica. Inclui objetivos, conteúdo, exercícios e resumo.',
                'category' => 'education',
                'icon' => 'graduation-cap',
                'is_premium' => false,
                'slides' => [
                    [
                        'title' => 'Capa',
                        'content' => "# [Título da Aula]\n\n## [Subtítulo ou Módulo]\n\n---\n\n**Professor:** [Seu Nome]\n**Data:** [Data]\n**Duração:** XX minutos\n\n🎓 *[Nome do Curso/Disciplina]*",
                    ],
                    [
                        'title' => 'Objetivos',
                        'content' => "# Objetivos de Aprendizagem\n\n## Ao final desta aula, você será capaz de:\n\n1. 🎯 **Objetivo 1:** Descreva o primeiro objetivo\n\n2. 🎯 **Objetivo 2:** Descreva o segundo objetivo\n\n3. 🎯 **Objetivo 3:** Descreva o terceiro objetivo\n\n---\n\n⏱️ **Pré-requisitos:** [Liste conhecimentos prévios necessários]",
                    ],
                    [
                        'title' => 'Agenda',
                        'content' => "# Agenda da Aula\n\n## O que vamos cobrir hoje\n\n| Tempo | Tópico |\n|-------|--------|\n| 10 min | Introdução e contexto |\n| 20 min | Conceitos principais |\n| 15 min | Demonstração prática |\n| 10 min | Exercício em grupo |\n| 5 min | Resumo e próximos passos |\n\n💡 *Perguntas são bem-vindas a qualquer momento!*",
                    ],
                    [
                        'title' => 'Conceito 1',
                        'content' => "# [Nome do Conceito]\n\n## Definição\n\n> \"Definição formal do conceito\"\n\n### Características Principais\n\n- **Característica 1:** Explicação\n- **Característica 2:** Explicação\n- **Característica 3:** Explicação\n\n### Exemplo Prático\n\n```\nExemplo de código ou demonstração\n```",
                    ],
                    [
                        'title' => 'Conceito 2',
                        'content' => "# [Segundo Conceito]\n\n## Como funciona?\n\n### Passo a Passo\n\n1. **Passo 1:** Descrição detalhada\n2. **Passo 2:** Descrição detalhada\n3. **Passo 3:** Descrição detalhada\n\n### Diagrama\n\n```\n[A] → [B] → [C]\n ↓     ↓     ↓\n[X]   [Y]   [Z]\n```\n\n⚠️ **Atenção:** Ponto importante a lembrar",
                    ],
                    [
                        'title' => 'Exercício',
                        'content' => "# Exercício Prático\n\n## Vamos praticar!\n\n### Desafio\n\n📝 **Tarefa:** Descrição do exercício\n\n**Instruções:**\n1. Passo 1 do exercício\n2. Passo 2 do exercício\n3. Passo 3 do exercício\n\n⏱️ **Tempo:** 10 minutos\n\n👥 **Formato:** Individual / Em duplas / Em grupo\n\n---\n\n💡 *Dica: [Dica útil para o exercício]*",
                    ],
                    [
                        'title' => 'Resumo',
                        'content' => "# Resumo da Aula\n\n## O que aprendemos hoje\n\n### Pontos-Chave\n\n✅ **Conceito 1:** Resumo em uma frase\n\n✅ **Conceito 2:** Resumo em uma frase\n\n✅ **Aplicação:** Como usar no dia a dia\n\n### Próxima Aula\n\n📚 **Tema:** [Título da próxima aula]\n📖 **Leitura:** [Material recomendado]",
                    ],
                    [
                        'title' => 'Recursos',
                        'content' => "# Recursos Adicionais\n\n## Para aprofundar o conhecimento\n\n### Leituras Recomendadas\n\n- 📖 [Título do Livro] - Autor\n- 📄 [Título do Artigo] - Link\n\n### Vídeos\n\n- 🎬 [Título do Vídeo] - Plataforma\n\n### Ferramentas\n\n- 🛠️ [Nome da Ferramenta] - descrição\n\n---\n\n📧 **Dúvidas?** email@professor.com\n\n*Obrigado pela participação! 🙏*",
                    ],
                ],
            ],

            // ========================================
            // RELATÓRIO EXECUTIVO
            // ========================================
            [
                'name' => 'Relatório Executivo',
                'slug' => 'relatorio-executivo',
                'description' => 'Template profissional para relatórios de negócios, análises e resultados trimestrais.',
                'category' => 'report',
                'icon' => 'bar-chart',
                'is_premium' => false,
                'slides' => [
                    [
                        'title' => 'Capa',
                        'content' => "# Relatório Executivo\n\n## [Período/Trimestre] 2024\n\n---\n\n**Empresa:** [Nome da Empresa]\n**Departamento:** [Área]\n**Data:** [Data]\n\n*Confidencial - Uso Interno*",
                    ],
                    [
                        'title' => 'Sumário Executivo',
                        'content' => "# Sumário Executivo\n\n## Destaques do Período\n\n### Principais Resultados\n\n📈 **Receita:** R$ X.X milhões (+XX% vs período anterior)\n\n📊 **Margem:** XX% (meta: XX%)\n\n👥 **Clientes:** X.XXX (+XXX novos)\n\n### Conclusão\n\n> Resumo em 2-3 frases dos principais pontos do relatório e recomendações.",
                    ],
                    [
                        'title' => 'KPIs',
                        'content' => "# Indicadores de Performance\n\n## KPIs do Período\n\n| Métrica | Atual | Meta | Status |\n|---------|-------|------|--------|\n| Receita | R$ Xm | R$ Xm | 🟢 |\n| Margem | XX% | XX% | 🟡 |\n| NPS | XX | XX | 🟢 |\n| Churn | X% | X% | 🔴 |\n| CAC | R$ XXX | R$ XXX | 🟢 |\n\n**Legenda:** 🟢 Acima da meta | 🟡 Na meta | 🔴 Abaixo da meta",
                    ],
                    [
                        'title' => 'Análise Financeira',
                        'content' => "# Análise Financeira\n\n## Resultados do Período\n\n### Receita por Categoria\n\n| Categoria | Valor | % Total |\n|-----------|-------|--------|\n| Produto A | R$ Xm | XX% |\n| Produto B | R$ Xm | XX% |\n| Serviços | R$ Xm | XX% |\n\n### Evolução Mensal\n\n```\nJan: ████████░░ R$ X.Xm\nFev: █████████░ R$ X.Xm\nMar: ██████████ R$ X.Xm\n```",
                    ],
                    [
                        'title' => 'Desafios',
                        'content' => "# Desafios e Riscos\n\n## Pontos de Atenção\n\n### Desafios Identificados\n\n1. ⚠️ **[Desafio 1]**\n   - Impacto: Alto/Médio/Baixo\n   - Ação: Descrição da ação\n\n2. ⚠️ **[Desafio 2]**\n   - Impacto: Alto/Médio/Baixo\n   - Ação: Descrição da ação\n\n### Mitigação de Riscos\n\n| Risco | Probabilidade | Plano |\n|-------|--------------|-------|\n| Risco 1 | Alta | Ação X |\n| Risco 2 | Média | Ação Y |",
                    ],
                    [
                        'title' => 'Próximos Passos',
                        'content' => "# Próximos Passos\n\n## Plano de Ação\n\n### Curto Prazo (30 dias)\n\n- [ ] Ação 1 - Responsável: [Nome]\n- [ ] Ação 2 - Responsável: [Nome]\n\n### Médio Prazo (90 dias)\n\n- [ ] Ação 3 - Responsável: [Nome]\n- [ ] Ação 4 - Responsável: [Nome]\n\n### Investimentos Necessários\n\n| Item | Valor | Prazo |\n|------|-------|-------|\n| [Item 1] | R$ XXk | XX dias |\n| [Item 2] | R$ XXk | XX dias |",
                    ],
                ],
            ],

            // ========================================
            // PORTFOLIO
            // ========================================
            [
                'name' => 'Portfolio Criativo',
                'slug' => 'portfolio-criativo',
                'description' => 'Mostre seus melhores trabalhos e projetos de forma profissional e atraente.',
                'category' => 'portfolio',
                'icon' => 'briefcase',
                'is_premium' => true,
                'slides' => [
                    [
                        'title' => 'Capa',
                        'content' => "# [Seu Nome]\n\n## [Sua Especialidade]\n\n---\n\n🌐 seusite.com\n📧 email@exemplo.com\n💼 linkedin.com/in/seunome\n\n*Portfolio 2024*",
                    ],
                    [
                        'title' => 'Sobre Mim',
                        'content' => "# Sobre Mim\n\n## Prazer, sou [Nome]!\n\n> \"Sua frase de efeito ou filosofia de trabalho\"\n\n### Minha História\n\nBreve parágrafo sobre sua trajetória, experiência e o que te motiva.\n\n### Especialidades\n\n- 🎨 [Habilidade 1]\n- 💻 [Habilidade 2]\n- 📊 [Habilidade 3]\n\n**Anos de experiência:** X+",
                    ],
                    [
                        'title' => 'Projeto 1',
                        'content' => "# [Nome do Projeto]\n\n## [Cliente/Empresa]\n\n### O Desafio\n\nDescrição do problema ou necessidade do cliente.\n\n### A Solução\n\nComo você abordou e resolveu o desafio.\n\n### Resultados\n\n- 📈 **Métrica 1:** +XX%\n- 👥 **Métrica 2:** XXX usuários\n- ⭐ **Métrica 3:** X.X de rating\n\n🔗 *[Ver projeto ao vivo]*",
                    ],
                    [
                        'title' => 'Projeto 2',
                        'content' => "# [Nome do Projeto]\n\n## [Cliente/Empresa]\n\n### Escopo\n\n- Entregável 1\n- Entregável 2\n- Entregável 3\n\n### Tecnologias/Ferramentas\n\n`Ferramenta 1` `Ferramenta 2` `Ferramenta 3`\n\n### Depoimento do Cliente\n\n> \"Depoimento do cliente sobre o trabalho realizado.\"\n> \n> — Nome, Cargo na Empresa",
                    ],
                    [
                        'title' => 'Habilidades',
                        'content' => "# Habilidades\n\n## Minhas Competências\n\n### Técnicas\n\n| Habilidade | Nível |\n|------------|-------|\n| [Skill 1] | ████████░░ 80% |\n| [Skill 2] | █████████░ 90% |\n| [Skill 3] | ███████░░░ 70% |\n\n### Soft Skills\n\n- 🤝 Comunicação\n- 🎯 Resolução de problemas\n- ⏰ Gestão de tempo\n- 👥 Trabalho em equipe",
                    ],
                    [
                        'title' => 'Contato',
                        'content' => "# Vamos Trabalhar Juntos?\n\n## Entre em Contato\n\n### Disponível para:\n\n- ✅ Projetos freelance\n- ✅ Consultoria\n- ✅ Colaborações\n\n### Contatos\n\n📧 **Email:** seu@email.com\n\n📱 **WhatsApp:** (XX) XXXXX-XXXX\n\n💼 **LinkedIn:** /in/seunome\n\n🌐 **Site:** www.seusite.com\n\n---\n\n*Respondo em até 24 horas!*",
                    ],
                ],
            ],

            // ========================================
            // PROPOSTA COMERCIAL
            // ========================================
            [
                'name' => 'Proposta Comercial',
                'slug' => 'proposta-comercial',
                'description' => 'Template profissional para propostas comerciais e orçamentos para clientes.',
                'category' => 'proposal',
                'icon' => 'file-text',
                'is_premium' => true,
                'slides' => [
                    [
                        'title' => 'Capa',
                        'content' => "# Proposta Comercial\n\n## [Nome do Projeto/Serviço]\n\n---\n\n**Para:** [Nome do Cliente]\n**De:** [Sua Empresa]\n**Data:** [Data]\n**Validade:** 30 dias\n\n*Proposta #[Número]*",
                    ],
                    [
                        'title' => 'Sobre Nós',
                        'content' => "# Quem Somos\n\n## [Nome da Empresa]\n\n### Nossa História\n\nBreve descrição da empresa, missão e valores.\n\n### Números que Importam\n\n| Métrica | Valor |\n|---------|-------|\n| Anos no mercado | X+ |\n| Clientes atendidos | XXX+ |\n| Projetos entregues | X.XXX+ |\n| Satisfação | XX% |\n\n### Clientes\n\n*Logo 1 | Logo 2 | Logo 3 | Logo 4*",
                    ],
                    [
                        'title' => 'Entendimento',
                        'content' => "# Entendimento do Projeto\n\n## O que você precisa\n\n### Contexto\n\nDescrição do cenário atual do cliente e seus desafios.\n\n### Objetivos\n\n1. 🎯 Objetivo principal\n2. 🎯 Objetivo secundário\n3. 🎯 Objetivo terciário\n\n### Critérios de Sucesso\n\n- ✅ KPI 1: [Descrição]\n- ✅ KPI 2: [Descrição]",
                    ],
                    [
                        'title' => 'Nossa Solução',
                        'content' => "# Nossa Solução\n\n## Como vamos ajudar\n\n### Escopo do Projeto\n\n#### Fase 1: [Nome]\n- Entregável 1\n- Entregável 2\n\n#### Fase 2: [Nome]\n- Entregável 3\n- Entregável 4\n\n#### Fase 3: [Nome]\n- Entregável 5\n- Entregável 6\n\n### Diferenciais\n\n- ⭐ [Diferencial 1]\n- ⭐ [Diferencial 2]\n- ⭐ [Diferencial 3]",
                    ],
                    [
                        'title' => 'Cronograma',
                        'content' => "# Cronograma\n\n## Linha do Tempo\n\n| Fase | Atividade | Duração |\n|------|-----------|--------|\n| 1 | Kickoff e Discovery | 1 semana |\n| 2 | Desenvolvimento | 4 semanas |\n| 3 | Testes e Ajustes | 1 semana |\n| 4 | Entrega e Treinamento | 1 semana |\n\n**Prazo Total:** 7 semanas\n\n⚠️ *Cronograma sujeito a ajustes após kickoff*",
                    ],
                    [
                        'title' => 'Investimento',
                        'content' => "# Investimento\n\n## Valores\n\n### Opção 1: [Nome]\n\n| Item | Valor |\n|------|-------|\n| [Serviço 1] | R$ X.XXX |\n| [Serviço 2] | R$ X.XXX |\n| **Total** | **R$ XX.XXX** |\n\n### Condições de Pagamento\n\n- 50% na assinatura\n- 50% na entrega\n\n*ou*\n\n- 3x de R$ X.XXX\n\n✅ **Incluso:** Suporte por 30 dias\n\n❌ **Não incluso:** [Item]",
                    ],
                    [
                        'title' => 'Próximos Passos',
                        'content' => "# Próximos Passos\n\n## Como seguir\n\n### 1. Aprovação\n\nResponda este email confirmando aceite.\n\n### 2. Assinatura\n\nContrato enviado em 24h após aprovação.\n\n### 3. Kickoff\n\nReunião de início em até 5 dias úteis.\n\n---\n\n📧 **Email:** contato@empresa.com\n📱 **Telefone:** (XX) XXXX-XXXX\n\n**[Nome do Responsável]**\n*Cargo | Empresa*\n\n*Estamos animados para trabalhar com você! 🚀*",
                    ],
                ],
            ],
        ];

        foreach ($templates as $templateData) {
            Template::updateOrCreate(
                ['slug' => $templateData['slug']],
                $templateData
            );
        }
    }
}

