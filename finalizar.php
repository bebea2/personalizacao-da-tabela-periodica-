<?php
require_once __DIR__ . '/auth.php';

// Requer login conforme regras do negócio
requer_login();
$usuario = obter_usuario();

$projetoId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$projetoTitulo = "Minha Tabela Periódica";
$configJson = "null";

if ($projetoId > 0) {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT titulo, config_json FROM projetos WHERE id = :id AND usuario_id = :uid LIMIT 1");
        $stmt->execute([':id' => $projetoId, ':uid' => $usuario['id']]);
        $proj = $stmt->fetch();
        if ($proj) {
            $projetoTitulo = $proj['titulo'];
            $configJson = $proj['config_json'];
        }
    } catch (Exception $e) {
        // Fallback
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($projetoTitulo) ?> - Tabela Concluída | PeriodicArt</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    
    <!-- Bibliotecas para exportação vetorial, PNG de alta definição e PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f4f3fb',
                            100: '#eae8f8',
                            200: '#d7d4f3',
                            300: '#b8b2eb',
                            400: '#9488df',
                            500: '#7563d3',
                            600: '#5e4bc4',
                            700: '#503ea7',
                            800: '#423489',
                            900: '#382e6f',
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    }
                }
            }
        }
    </script>
    <style>
        .tabela-grid-final {
            display: grid;
            grid-template-columns: repeat(18, minmax(0, 1fr));
            gap: var(--element-gap, 4px);
            width: 100%;
            user-select: none;
        }

        .elemento-card-final {
            aspect-ratio: 1 / 1.15;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: var(--card-padding, 4px);
            border-radius: var(--card-radius, 8px);
            border-width: var(--card-border-width, 1.5px);
            border-style: solid;
            box-shadow: var(--card-shadow, 0 1px 2px rgba(0,0,0,0.05));
        }

        @media print {
            body {
                background: white !important;
                color: black !important;
                padding: 0 !important;
            }
            header, footer, .nao-imprimir {
                display: none !important;
            }
            #container-exportacao {
                box-shadow: none !important;
                border: none !important;
                max-width: 100% !important;
                width: 100% !important;
                padding: 0 !important;
            }
        }
    </style>
</head>
<body class="bg-[#f4f5fa] text-slate-800 min-h-screen flex flex-col font-sans">

    <!-- Header (Exatamente como a Tela 3 do Mockup) -->
    <header class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between sticky top-0 z-40 shadow-sm nao-imprimir">
        <a href="index.php" class="flex items-center gap-2 group">
            <span class="text-3xl text-brand-600 transition group-hover:rotate-45">⚛️</span>
            <span class="text-2xl font-black text-slate-800 group-hover:text-brand-600 transition">PeriodicArt</span>
        </a>

        <div class="flex items-center gap-3">
            <span class="text-sm font-extrabold text-brand-600 bg-brand-50 border border-brand-200 px-3 py-1.5 rounded-full flex items-center gap-1.5">
                <span>Projeto concluído!</span>
                <span>🎉</span>
            </span>
            <a href="meus-projetos.php" class="text-xs font-bold text-slate-600 hover:text-brand-600 px-3 py-2 transition hidden sm:inline">
                📂 Meus Projetos
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 max-w-5xl w-full mx-auto p-4 sm:p-6 my-4 flex flex-col items-center">
        
        <!-- Título e Subtítulo (Como no Mockup) -->
        <div class="text-center mb-6 nao-imprimir">
            <div class="inline-flex items-center gap-2 text-brand-600 font-extrabold text-lg sm:text-xl mb-1">
                <span>✨</span>
                <h2>Sua tabela periódica está pronta!</h2>
                <span>✨</span>
            </div>
            <p class="text-slate-500 text-sm font-medium">Veja como ficou:</p>
        </div>

        <!-- Moldura da Tabela Periódica Renderizada (Pronta para Exportação) -->
        <div id="container-exportacao" 
             class="w-full bg-white rounded-3xl border border-slate-200/90 shadow-2xl p-4 sm:p-8 relative overflow-hidden transition-all duration-300 mb-8"
             style="min-height: 520px;">
            
            <!-- Logo da Empresa (se ativo) -->
            <div id="final-logo-empresa" class="absolute hidden z-30 pointer-events-none">
                <img id="final-img-logo" src="" alt="Logo" class="max-h-24 object-contain">
            </div>

            <!-- Título do Projeto na Tabela -->
            <div class="text-center mb-6 select-none">
                <h1 id="final-titulo-texto" class="text-xl sm:text-3xl font-black text-slate-800 tracking-tight uppercase">
                    <?= htmlspecialchars($projetoTitulo) ?>
                </h1>
                <p class="text-xs text-slate-500 font-medium">
                    Tabela Periódica Oficial IUPAC - Vetorizada em Alta Definição
                </p>
            </div>

            <!-- Grids dos 118 Elementos Químicos -->
            <div id="final-grid-principal" class="tabela-grid-final mb-6">
                <!-- Gerado dinamicamente -->
            </div>

            <!-- Lantanídeos e Actinídeos -->
            <div class="pt-4 border-t border-dashed border-slate-200/80">
                <div class="text-[9px] font-extrabold text-slate-400 uppercase tracking-widest text-center mb-2">
                    Lantanídeos (57-71) & Actinídeos (89-103)
                </div>
                <div id="final-grid-f-block" class="tabela-grid-final">
                    <!-- Gerado dinamicamente -->
                </div>
            </div>

            <!-- Camada de Adesivos e Decorações -->
            <div id="final-camada-decorativa" class="absolute inset-0 pointer-events-none overflow-hidden rounded-3xl">
                <!-- Adesivos renderizados com posições exatas -->
            </div>

        </div>

        <!-- Grupo de Botões Principais de Ação (Exatamente como o Mockup) -->
        <div class="w-full max-w-md space-y-3 nao-imprimir">
            
            <!-- Botão Baixar Imagem (Dropdown / Modal) -->
            <div class="relative">
                <button onclick="alternarMenuDownload()" id="btn-menu-download"
                        class="w-full py-4 px-6 bg-brand-600 hover:bg-brand-700 active:scale-[0.99] text-white font-extrabold rounded-2xl shadow-xl shadow-brand-600/30 flex items-center justify-center gap-2 text-base transition">
                    <span>📥</span>
                    <span>Baixar imagem</span>
                    <span class="text-xs ml-1">▼</span>
                </button>

                <!-- Menu de Opções de Formato de Download -->
                <div id="menu-download-opcoes" class="hidden absolute bottom-full left-0 right-0 mb-2 bg-white border border-slate-200 rounded-2xl shadow-2xl p-2 z-50 space-y-1">
                    <button onclick="baixarImagem('png')" class="w-full text-left px-4 py-2.5 rounded-xl hover:bg-brand-50 text-slate-700 font-bold text-xs sm:text-sm flex items-center justify-between transition">
                        <span>🖼️ Baixar como PNG (Alta Resolução 4K)</span>
                        <span class="text-[10px] text-brand-600 uppercase bg-brand-50 px-2 py-0.5 rounded">Recomendado</span>
                    </button>
                    <button onclick="baixarImagem('jpeg')" class="w-full text-left px-4 py-2.5 rounded-xl hover:bg-brand-50 text-slate-700 font-bold text-xs sm:text-sm flex items-center justify-between transition">
                        <span>📷 Baixar como JPEG</span>
                        <span class="text-[10px] text-slate-400">Leve</span>
                    </button>
                    <button onclick="baixarComoSVG()" class="w-full text-left px-4 py-2.5 rounded-xl hover:bg-brand-50 text-slate-700 font-bold text-xs sm:text-sm flex items-center justify-between transition">
                        <span>📐 Baixar como SVG Vetorial</span>
                        <span class="text-[10px] text-purple-600 uppercase bg-purple-50 px-2 py-0.5 rounded">Vetor Puro</span>
                    </button>
                    <button onclick="baixarComoPDF()" class="w-full text-left px-4 py-2.5 rounded-xl hover:bg-brand-50 text-slate-700 font-bold text-xs sm:text-sm flex items-center justify-between transition">
                        <span>📄 Baixar como PDF (A4 Paisagem)</span>
                        <span class="text-[10px] text-emerald-600 uppercase bg-emerald-50 px-2 py-0.5 rounded">Impressão</span>
                    </button>
                    <button onclick="baixarComoJSON()" class="w-full text-left px-4 py-2.5 rounded-xl hover:bg-brand-50 text-slate-700 font-bold text-xs sm:text-sm flex items-center justify-between transition">
                        <span>💾 Salvar arquivo de Backup (.json)</span>
                        <span class="text-[10px] text-slate-400">Backup</span>
                    </button>
                </div>
            </div>

            <!-- Botão Imprimir -->
            <button onclick="window.print()" 
                    class="w-full py-3.5 px-6 bg-white hover:bg-slate-50 active:scale-[0.99] border border-slate-300 text-slate-700 font-bold rounded-2xl shadow-sm flex items-center justify-center gap-2 text-sm transition">
                <span>🖨️</span>
                <span>Imprimir</span>
            </button>

            <!-- Botão Editar Novamente (Regra do Core: pode voltar e arrumar de novo) -->
            <a href="editor.php?id=<?= $projetoId ?>" 
               class="w-full py-3.5 px-6 bg-white hover:bg-slate-50 active:scale-[0.99] border border-slate-300 text-slate-700 font-bold rounded-2xl shadow-sm flex items-center justify-center gap-2 text-sm transition">
                <span>✏️</span>
                <span>Editar novamente</span>
            </a>

            <!-- Botão Meus Projetos -->
            <a href="meus-projetos.php" 
               class="w-full py-3 px-6 text-slate-500 hover:text-brand-600 font-semibold text-xs text-center block transition">
                📂 Ver todas as minhas tabelas salvas
            </a>

        </div>

        <!-- Compartilhe seu projeto (Como no Mockup) -->
        <div class="mt-12 text-center nao-imprimir">
            <h3 class="text-sm font-bold text-slate-700 mb-3">Compartilhe seu projeto!</h3>
            <div class="flex items-center justify-center gap-4 text-2xl text-slate-600">
                <a href="https://instagram.com" target="_blank" title="Instagram" class="hover:text-pink-600 transition p-2 bg-white rounded-xl border border-slate-200 hover:border-pink-300">
                    📸
                </a>
                <a href="https://api.whatsapp.com/send?text=Veja%20a%20tabela%20peri%C3%B3dica%20personalizada%20que%20criei%20no%20PeriodicArt!" target="_blank" title="WhatsApp" class="hover:text-emerald-600 transition p-2 bg-white rounded-xl border border-slate-200 hover:border-emerald-300">
                    💬
                </a>
                <a href="https://tiktok.com" target="_blank" title="TikTok" class="hover:text-slate-900 transition p-2 bg-white rounded-xl border border-slate-200 hover:border-slate-800">
                    🎵
                </a>
                <button onclick="copiarLinkCompartilhamento()" title="Copiar Link" class="hover:text-brand-600 transition p-2 bg-white rounded-xl border border-slate-200 hover:border-brand-300">
                    🔗
                </button>
            </div>
            <div id="toast-copiado" class="hidden text-xs text-emerald-600 font-bold mt-2">
                Link copiado para a área de transferência!
            </div>
        </div>

    </main>

    <!-- Dados dos 118 Elementos -->
    <script src="js_elementos.js"></script>
    <script>
        // Carrega configurações: primeiro tenta do sessionStorage (edição fresca), senão do banco
        let configBanco = <?= $configJson ?>;
        let configSession = null;
        try {
            configSession = JSON.parse(sessionStorage.getItem('projeto_finalizado_config'));
        } catch(e) {}

        const config = configSession || configBanco || {
            titulo: "Minha Tabela Periódica",
            gap: 4,
            padding: 4,
            borderRadius: '8px',
            borderWidth: '1.5px',
            corTexto: '#1e293b',
            corBorda: '#cbd5e1',
            sombra: '0 1px 2px rgba(0,0,0,0.05)',
            fundoTipo: 'lavanda',
            mostrarMassa: true,
            mostrarNome: true,
            coresFamilias: {
                'nao-metal': '#fbcfe8',
                'gas-nobre': '#e9d5ff',
                'metal-alcalino': '#fed7aa',
                'alcalino-terroso': '#fef08a',
                'semimetal': '#bbf7d0',
                'halogenio': '#cffafe',
                'metal-transicao': '#bfdbfe',
                'pos-transicao': '#bae6fd',
                'lantanideo': '#ddd6fe',
                'actinideo': '#f5d0fe'
            },
            elementosCoresCustom: {},
            adesivos: []
        };

        document.addEventListener('DOMContentLoaded', () => {
            aplicarConfiguracoesFinais();
            renderizarTabelaFinal();
            renderizarAdesivosFinais();
        });

        function aplicarConfiguracoesFinais() {
            const container = document.getElementById('container-exportacao');
            document.documentElement.style.setProperty('--card-radius', config.borderRadius || '8px');
            document.documentElement.style.setProperty('--card-border-width', config.borderWidth || '1.5px');
            document.documentElement.style.setProperty('--card-shadow', config.sombra || 'none');
            document.documentElement.style.setProperty('--element-gap', `${config.gap || 4}px`);
            document.documentElement.style.setProperty('--card-padding', `${config.padding || 4}px`);

            if (config.titulo) {
                document.getElementById('final-titulo-texto').innerText = config.titulo;
            }

            // Aplicar fundo selecionado
            if (config.fundoTipo === 'branco') {
                container.style.background = '#ffffff';
            } else if (config.fundoTipo === 'lavanda') {
                container.style.background = 'linear-gradient(135deg, #f4f3fb 0%, #eae8f8 100%)';
            } else if (config.fundoTipo === 'montanha') {
                container.style.background = 'linear-gradient(180deg, #fbcfe8 0%, #c4b5fd 40%, #818cf8 100%)';
            } else if (config.fundoTipo === 'galaxia') {
                container.style.background = 'linear-gradient(135deg, #020617 0%, #1e1b4b 50%, #0f172a 100%)';
            } else if (config.fundoCor) {
                container.style.background = config.fundoCor;
            }

            // Logo da empresa
            if (config.logo && config.logo.ativo && config.logo.src) {
                const logoEl = document.getElementById('final-logo-empresa');
                const img = document.getElementById('final-img-logo');
                img.src = config.logo.src;
                img.style.maxWidth = `${config.logo.largura || 120}px`;
                img.style.opacity = (config.logo.opacidade || 100) / 100;
                logoEl.style.left = `${config.logo.x}px`;
                logoEl.style.top = `${config.logo.y}px`;
                logoEl.classList.remove('hidden');
            }
        }

        function renderizarTabelaFinal() {
            const gridPrincipal = document.getElementById('final-grid-principal');
            const gridFBlock = document.getElementById('final-grid-f-block');
            gridPrincipal.innerHTML = '';
            gridFBlock.innerHTML = '';

            const mapaPosicoes = {};
            window.TABELA_ELEMENTOS.forEach(el => {
                mapaPosicoes[`${el.row}_${el.col}`] = el;
            });

            // Grid Principal 7 x 18
            for (let r = 1; r <= 7; r++) {
                for (let c = 1; c <= 18; c++) {
                    const el = mapaPosicoes[`${r}_${c}`];
                    if (el) {
                        gridPrincipal.appendChild(criarCardElementoFinal(el));
                    } else if (r === 6 && c === 3) {
                        const placeholder = document.createElement('div');
                        placeholder.className = 'elemento-card-final flex items-center justify-center text-center font-bold text-[7px] bg-purple-50 text-purple-700 border-purple-200';
                        placeholder.innerHTML = '<span>57-71<br>La-Lu</span>';
                        gridPrincipal.appendChild(placeholder);
                    } else if (r === 7 && c === 3) {
                        const placeholder = document.createElement('div');
                        placeholder.className = 'elemento-card-final flex items-center justify-center text-center font-bold text-[7px] bg-fuchsia-50 text-fuchsia-700 border-fuchsia-200';
                        placeholder.innerHTML = '<span>89-103<br>Ac-Lr</span>';
                        gridPrincipal.appendChild(placeholder);
                    } else {
                        const vazio = document.createElement('div');
                        gridPrincipal.appendChild(vazio);
                    }
                }
            }

            // Grid Lantanídeos e Actinídeos
            for (let r = 9; r <= 10; r++) {
                gridFBlock.appendChild(document.createElement('div'));
                gridFBlock.appendChild(document.createElement('div'));
                for (let c = 3; c <= 17; c++) {
                    const el = mapaPosicoes[`${r}_${c}`];
                    if (el) {
                        gridFBlock.appendChild(criarCardElementoFinal(el));
                    } else {
                        gridFBlock.appendChild(document.createElement('div'));
                    }
                }
                gridFBlock.appendChild(document.createElement('div'));
            }
        }

        function criarCardElementoFinal(el) {
            const card = document.createElement('div');
            card.className = 'elemento-card-final';
            const corBg = (config.elementosCoresCustom && config.elementosCoresCustom[el.numero]) 
                          || (config.coresFamilias && config.coresFamilias[el.categoria]) 
                          || '#e2e8f0';
            
            card.style.backgroundColor = corBg;
            card.style.borderColor = config.corBorda || '#cbd5e1';
            card.style.color = config.corTexto || '#1e293b';

            card.innerHTML = `
                <div class="flex items-center justify-between text-[6px] sm:text-[8px] font-mono leading-none">
                    <span class="font-bold opacity-80">${el.numero}</span>
                    <span class="text-[5px] sm:text-[7px] opacity-70 ${config.mostrarMassa !== false ? '' : 'hidden'}">${el.massa}</span>
                </div>
                <div class="text-center font-black text-xs sm:text-sm lg:text-base tracking-tight leading-tight my-auto">
                    ${el.simbolo}
                </div>
                <div class="text-center text-[5px] sm:text-[7px] truncate font-semibold leading-none opacity-90 ${config.mostrarNome !== false ? '' : 'hidden'}">
                    ${el.nome}
                </div>
            `;
            return card;
        }

        function renderizarAdesivosFinais() {
            const camada = document.getElementById('final-camada-decorativa');
            camada.innerHTML = '';
            if (Array.isArray(config.adesivos)) {
                config.adesivos.forEach(item => {
                    const el = document.createElement('div');
                    el.className = 'absolute text-center flex items-center justify-center';
                    el.style.left = `${item.x}px`;
                    el.style.top = `${item.y}px`;
                    el.style.fontSize = `${item.tamanho}px`;
                    el.style.zIndex = 35;

                    if (item.tipo === 'imagem') {
                        el.innerHTML = `<img src="${item.conteudo}" class="w-full h-full object-contain">`;
                        el.style.width = `${item.tamanho}px`;
                        el.style.height = `${item.tamanho}px`;
                    } else {
                        el.innerText = item.conteudo;
                    }
                    camada.appendChild(el);
                });
            }
        }

        function alternarMenuDownload() {
            const menu = document.getElementById('menu-download-opcoes');
            menu.classList.toggle('hidden');
        }

        // EXPORTAÇÃO 1: PNG / JPEG de Alta Resolução via Canvas
        async function baixarImagem(formato = 'png') {
            alternarMenuDownload();
            const alvo = document.getElementById('container-exportacao');
            const btn = document.getElementById('btn-menu-download');
            const textoOriginal = btn.innerHTML;
            btn.innerHTML = "<span>⏳ Processando exportação...</span>";

            try {
                const canvas = await html2canvas(alvo, {
                    scale: 3, // Alta resolução 300 DPI
                    useCORS: true,
                    allowTaint: true,
                    backgroundColor: null
                });

                const link = document.createElement('a');
                link.download = `tabela_periodica_${Date.now()}.${formato}`;
                link.href = canvas.toDataURL(formato === 'jpeg' ? 'image/jpeg' : 'image/png', 0.95);
                link.click();
            } catch (err) {
                console.error(err);
                alert("Erro ao exportar imagem. Tente novamente.");
            } finally {
                btn.innerHTML = textoOriginal;
            }
        }

        // EXPORTAÇÃO 2: PDF Oficial em Paisagem (A4)
        async function baixarComoPDF() {
            alternarMenuDownload();
            const alvo = document.getElementById('container-exportacao');
            const btn = document.getElementById('btn-menu-download');
            const textoOriginal = btn.innerHTML;
            btn.innerHTML = "<span>⏳ Gerando PDF...</span>";

            try {
                const canvas = await html2canvas(alvo, { scale: 2.5, useCORS: true });
                const imgData = canvas.toDataURL('image/png');
                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF({
                    orientation: 'landscape',
                    unit: 'mm',
                    format: 'a4'
                });

                const imgWidth = 280;
                const imgHeight = (canvas.height * imgWidth) / canvas.width;
                pdf.addImage(imgData, 'PNG', 8, 8, imgWidth, imgHeight);
                pdf.save(`tabela_periodica_${Date.now()}.pdf`);
            } catch(e) {
                console.error(e);
                alert("Erro ao gerar PDF.");
            } finally {
                btn.innerHTML = textoOriginal;
            }
        }

        // EXPORTAÇÃO 3: SVG Vetorial Puro (cabe em qualquer resolução)
        function baixarComoSVG() {
            alternarMenuDownload();
            const alvo = document.getElementById('container-exportacao');
            const svgContent = `
                <svg xmlns="http://www.w3.org/2000/svg" width="${alvo.offsetWidth}" height="${alvo.offsetHeight}">
                    <foreignObject width="100%" height="100%">
                        <div xmlns="http://www.w3.org/1999/xhtml">
                            ${alvo.outerHTML}
                        </div>
                    </foreignObject>
                </svg>
            `;
            const blob = new Blob([svgContent], { type: 'image/svg+xml;charset=utf-8' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `tabela_periodica_vetorial_${Date.now()}.svg`;
            a.click();
            URL.revokeObjectURL(url);
        }

        // EXPORTAÇÃO 4: JSON de Backup
        function baixarComoJSON() {
            alternarMenuDownload();
            const blob = new Blob([JSON.stringify(config, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `tabela_periodica_backup_${Date.now()}.json`;
            a.click();
            URL.revokeObjectURL(url);
        }

        function copiarLinkCompartilhamento() {
            navigator.clipboard.writeText(window.location.href);
            const toast = document.getElementById('toast-copiado');
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 3000);
        }
    </script>
</body>
</html>
