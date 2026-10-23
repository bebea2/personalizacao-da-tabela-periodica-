<?php
require_once __DIR__ . '/auth.php';

// Requer login conforme regras do negócio
requer_login();
$usuario = obter_usuario();

$projetoId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$projetoTitulo = "Projeto sem nome";
$projetoConfigJson = "null";

// Se tiver ID de projeto, carregar do banco de dados via PDO seguro
if ($projetoId > 0) {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT titulo, config_json FROM projetos WHERE id = :id AND usuario_id = :uid LIMIT 1");
        $stmt->execute([':id' => $projetoId, ':uid' => $usuario['id']]);
        $proj = $stmt->fetch();
        if ($proj) {
            $projetoTitulo = $proj['titulo'];
            $projetoConfigJson = $proj['config_json'];
        }
    } catch (Exception $e) {
        // Ignora e usa padrão
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($projetoTitulo) ?> - Editor PeriodicArt</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <!-- Lucide Icons / SVG support -->
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
        /* Tabela Periódica em CSS Grid Estrito de 18 colunas */
        .tabela-grid {
            display: grid;
            grid-template-columns: repeat(18, minmax(0, 1fr));
            gap: var(--element-gap, 4px);
            width: 100%;
            user-select: none;
        }

        .elemento-card {
            aspect-ratio: 1 / 1.15;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: var(--card-padding, 4px);
            border-radius: var(--card-radius, 8px);
            border-width: var(--card-border-width, 1.5px);
            border-style: solid;
            transition: all 0.15s ease-in-out;
            cursor: pointer;
            position: relative;
            box-shadow: var(--card-shadow, 0 1px 2px rgba(0,0,0,0.05));
        }

        .elemento-card:hover {
            transform: translateY(-2px) scale(1.04);
            z-index: 20;
            box-shadow: 0 8px 16px -2px rgba(0, 0, 0, 0.15);
        }

        /* Estilo de arrastar adesivos e imagens */
        .item-decorativo {
            position: absolute;
            cursor: move;
            user-select: none;
            touch-action: none;
            transition: transform 0.05s ease-out;
        }

        .item-decorativo:hover {
            outline: 2px dashed #5e4bc4;
            outline-offset: 4px;
        }

        .item-decorativo.selecionado {
            outline: 2px solid #5e4bc4;
            outline-offset: 4px;
        }

        /* Barra de rolagem suave */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body class="bg-[#f4f5fa] text-slate-800 min-h-screen flex flex-col font-sans overflow-x-hidden">

    <!-- Topbar (Exatamente como Tela 2 do Mockup) -->
    <header class="bg-white border-b border-slate-200 px-4 py-2.5 flex items-center justify-between sticky top-0 z-40 shadow-sm">
        
        <!-- Esquerda: Logo e Botão Voltar -->
        <div class="flex items-center gap-3">
            <a href="index.php" title="Voltar ao início" class="flex items-center gap-2 group">
                <span class="text-2xl text-brand-600 transition group-hover:rotate-45">⚛️</span>
                <span class="text-xl font-extrabold text-slate-800 hidden sm:inline group-hover:text-brand-600 transition">PeriodicArt</span>
            </a>
            
            <div class="h-6 w-px bg-slate-200 hidden sm:block mx-1"></div>

            <!-- Título Editável do Projeto -->
            <div class="flex items-center gap-1.5 bg-slate-50 hover:bg-slate-100 border border-slate-200/80 rounded-xl px-3 py-1.5 transition">
                <input type="text" id="projeto-nome" value="<?= htmlspecialchars($projetoTitulo) ?>" 
                       class="bg-transparent font-bold text-slate-700 text-sm focus:outline-none w-32 sm:w-56" 
                       placeholder="Nome do projeto">
                <span class="text-xs text-slate-400 select-none">✏️</span>
            </div>
        </div>

        <!-- Centro: Desfazer / Refazer -->
        <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl">
            <button onclick="desfazerAcao()" title="Desfazer (Ctrl+Z)" class="p-1.5 text-slate-600 hover:text-slate-900 hover:bg-white rounded-lg transition text-base">
                ↶
            </button>
            <button onclick="refazerAcao()" title="Refazer (Ctrl+Y)" class="p-1.5 text-slate-600 hover:text-slate-900 hover:bg-white rounded-lg transition text-base">
                ↷
            </button>
        </div>

        <!-- Direita: Salvar e Finalizar -->
        <div class="flex items-center gap-2 sm:gap-3">
            <a href="meus-projetos.php" class="hidden md:inline-flex text-xs font-bold text-slate-600 hover:text-brand-600 px-3 py-2 transition">
                📂 Meus Projetos
            </a>

            <!-- Botão Salvar (MySQL/PDO) -->
            <button onclick="salvarProjetoNoBanco(false)" id="btn-salvar"
                    class="flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 active:scale-95 text-slate-700 font-bold px-3.5 py-2 rounded-xl text-xs sm:text-sm transition">
                <span>💾</span>
                <span id="btn-salvar-texto">Salvar</span>
            </button>

            <!-- Botão Finalizar (Vai para Tela 3) -->
            <button onclick="finalizarProjeto()" 
                    class="flex items-center gap-1.5 bg-brand-600 hover:bg-brand-700 active:scale-95 text-white font-extrabold px-4 sm:px-5 py-2 rounded-xl text-xs sm:text-sm shadow-md shadow-brand-600/25 transition">
                <span>✔️</span>
                <span>Finalizar</span>
            </button>

            <!-- Mobile Sidebar Toggle -->
            <button onclick="toggleMobileSidebar()" class="lg:hidden p-2 text-slate-600 hover:bg-slate-100 rounded-xl">
                ☰
            </button>
        </div>
    </header>

    <!-- Main Workspace Studio -->
    <div class="flex-1 flex overflow-hidden relative" style="min-height: calc(100vh - 58px);">

        <!-- Left Sidebar: Painel de Ferramentas / Estilos (Canva-like) -->
        <aside id="sidebar-ferramentas" 
               class="w-80 lg:w-96 bg-white border-r border-slate-200 flex flex-col z-30 transition-transform duration-300 absolute lg:static top-0 bottom-0 left-0 -translate-x-full lg:translate-x-0 shadow-xl lg:shadow-none">
            
            <!-- Barra de Abas das Ferramentas -->
            <div class="border-b border-slate-200 bg-slate-50/70 p-2 overflow-x-auto flex gap-1 scrollbar-none">
                <button onclick="abrirAbaSidebar('modelos')" id="tab-btn-modelos" class="tab-sidebar-btn px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1 bg-white shadow-sm text-brand-600">
                    <span>📑</span> Modelos
                </button>
                <button onclick="abrirAbaSidebar('cores')" id="tab-btn-cores" class="tab-sidebar-btn px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1 text-slate-600 hover:text-slate-900">
                    <span>🎨</span> Cores
                </button>
                <button onclick="abrirAbaSidebar('adesivos')" id="tab-btn-adesivos" class="tab-sidebar-btn px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1 text-slate-600 hover:text-slate-900">
                    <span>⭐</span> Adesivos
                </button>
                <button onclick="abrirAbaSidebar('fotos')" id="tab-btn-fotos" class="tab-sidebar-btn px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1 text-slate-600 hover:text-slate-900">
                    <span>🖼️</span> Fotos
                </button>
                <button onclick="abrirAbaSidebar('logo')" id="tab-btn-logo" class="tab-sidebar-btn px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1 text-slate-600 hover:text-slate-900">
                    <span>🏢</span> Logo
                </button>
                <button onclick="abrirAbaSidebar('quadrinhos')" id="tab-btn-quadrinhos" class="tab-sidebar-btn px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1 text-slate-600 hover:text-slate-900">
                    <span>🧩</span> Formato
                </button>
                <button onclick="abrirAbaSidebar('fundos')" id="tab-btn-fundos" class="tab-sidebar-btn px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1 text-slate-600 hover:text-slate-900">
                    <span>🌄</span> Fundos
                </button>
                <button onclick="abrirAbaSidebar('tamanho')" id="tab-btn-tamanho" class="tab-sidebar-btn px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1 text-slate-600 hover:text-slate-900">
                    <span>📐</span> Zoom
                </button>
            </div>

            <!-- Conteúdo da Sidebar (com Rolagem Independente) -->
            <div class="flex-1 overflow-y-auto p-4 space-y-6">

                <!-- 1. ABA: MODELOS PRONTOS (TEMPLATES) -->
                <div id="aba-conteudo-modelos" class="space-y-4">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-800 flex items-center gap-2">
                            <span>📑</span> Modelos Prontos
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Escolha um estilo base e decore por cima!</p>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5">
                        <button onclick="aplicarTemplate('pastel')" class="p-3 rounded-2xl border-2 border-brand-200 bg-brand-50/50 hover:bg-brand-50 text-left transition group">
                            <div class="flex gap-1 mb-2">
                                <span class="w-3.5 h-3.5 rounded bg-pink-300"></span>
                                <span class="w-3.5 h-3.5 rounded bg-purple-300"></span>
                                <span class="w-3.5 h-3.5 rounded bg-amber-200"></span>
                                <span class="w-3.5 h-3.5 rounded bg-emerald-200"></span>
                            </div>
                            <div class="font-bold text-xs text-slate-800">Pastel Aesthetic ✨</div>
                            <div class="text-[10px] text-slate-500">Estilo suave do mockup</div>
                        </button>

                        <button onclick="aplicarTemplate('classico')" class="p-3 rounded-2xl border border-slate-200 hover:border-brand-400 bg-white text-left transition group">
                            <div class="flex gap-1 mb-2">
                                <span class="w-3.5 h-3.5 rounded bg-blue-300"></span>
                                <span class="w-3.5 h-3.5 rounded bg-green-300"></span>
                                <span class="w-3.5 h-3.5 rounded bg-red-300"></span>
                                <span class="w-3.5 h-3.5 rounded bg-yellow-300"></span>
                            </div>
                            <div class="font-bold text-xs text-slate-800">Oficial IUPAC 🔬</div>
                            <div class="text-[10px] text-slate-500">Cores padrão científicas</div>
                        </button>

                        <button onclick="aplicarTemplate('cyberpunk')" class="p-3 rounded-2xl border border-slate-700 bg-slate-900 text-left transition group">
                            <div class="flex gap-1 mb-2">
                                <span class="w-3.5 h-3.5 rounded bg-cyan-400"></span>
                                <span class="w-3.5 h-3.5 rounded bg-fuchsia-400"></span>
                                <span class="w-3.5 h-3.5 rounded bg-lime-400"></span>
                                <span class="w-3.5 h-3.5 rounded bg-yellow-400"></span>
                            </div>
                            <div class="font-bold text-xs text-white">Cyberpunk Neon ⚡</div>
                            <div class="text-[10px] text-slate-400">Fundo escuro e brilho</div>
                        </button>

                        <button onclick="aplicarTemplate('minimalista')" class="p-3 rounded-2xl border border-slate-200 hover:border-brand-400 bg-white text-left transition group">
                            <div class="flex gap-1 mb-2">
                                <span class="w-3.5 h-3.5 rounded bg-slate-200"></span>
                                <span class="w-3.5 h-3.5 rounded bg-slate-300"></span>
                                <span class="w-3.5 h-3.5 rounded bg-slate-400"></span>
                                <span class="w-3.5 h-3.5 rounded bg-slate-600"></span>
                            </div>
                            <div class="font-bold text-xs text-slate-800">Minimalista P&B 🖤</div>
                            <div class="text-[10px] text-slate-500">Ideal para impressão</div>
                        </button>

                        <button onclick="aplicarTemplate('sunset')" class="p-3 rounded-2xl border border-slate-200 hover:border-brand-400 bg-white text-left transition group">
                            <div class="flex gap-1 mb-2">
                                <span class="w-3.5 h-3.5 rounded bg-rose-400"></span>
                                <span class="w-3.5 h-3.5 rounded bg-orange-400"></span>
                                <span class="w-3.5 h-3.5 rounded bg-amber-400"></span>
                                <span class="w-3.5 h-3.5 rounded bg-purple-400"></span>
                            </div>
                            <div class="font-bold text-xs text-slate-800">Sunset Horizon 🌅</div>
                            <div class="text-[10px] text-slate-500">Gradientes quentes</div>
                        </button>

                        <button onclick="aplicarTemplate('educacional')" class="p-3 rounded-2xl border border-slate-200 hover:border-brand-400 bg-white text-left transition group">
                            <div class="flex gap-1 mb-2">
                                <span class="w-3.5 h-3.5 rounded bg-blue-400"></span>
                                <span class="w-3.5 h-3.5 rounded bg-emerald-400"></span>
                                <span class="w-3.5 h-3.5 rounded bg-amber-400"></span>
                                <span class="w-3.5 h-3.5 rounded bg-red-400"></span>
                            </div>
                            <div class="font-bold text-xs text-slate-800">Kids & Escolas 🎈</div>
                            <div class="text-[10px] text-slate-500">Cores vibrantes</div>
                        </button>
                    </div>
                </div>

                <!-- 2. ABA: CORES -->
                <div id="aba-conteudo-cores" class="hidden space-y-5">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-800 flex items-center gap-2">
                            <span>🎨</span> Personalização de Cores
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Altere as cores das famílias e elementos</p>
                    </div>

                    <!-- Cores Gerais -->
                    <div class="space-y-3 bg-slate-50 p-3.5 rounded-2xl border border-slate-200/80">
                        <label class="text-xs font-bold text-slate-700 block">Cores Gerais da Tabela</label>
                        
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-600 font-medium">Cor do Texto dos Elementos</span>
                            <input type="color" id="cor-texto" value="#1e293b" onchange="atualizarEstilo('corTexto', this.value)" 
                                   class="w-8 h-8 rounded-lg cursor-pointer border border-slate-300">
                        </div>

                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-600 font-medium">Cor das Bordas dos Quadrinhos</span>
                            <input type="color" id="cor-borda" value="#cbd5e1" onchange="atualizarEstilo('corBorda', this.value)" 
                                   class="w-8 h-8 rounded-lg cursor-pointer border border-slate-300">
                        </div>
                    </div>

                    <!-- Cores por Família Química -->
                    <div class="space-y-2.5">
                        <label class="text-xs font-bold text-slate-700 block">Cores por Série Química (10 Famílias)</label>
                        
                        <div id="lista-cores-familias" class="space-y-2 text-xs">
                            <!-- Gerado dinamicamente via JS -->
                        </div>
                    </div>
                </div>

                <!-- 3. ABA: ADESIVOS (STICKERS) -->
                <div id="aba-conteudo-adesivos" class="hidden space-y-4">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-800 flex items-center gap-2">
                            <span>⭐</span> Adesivos e Stickers
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Clique em um adesivo para adicioná-lo na tabela</p>
                    </div>

                    <!-- Categorias de adesivos -->
                    <div class="space-y-4">
                        <div>
                            <div class="text-xs font-bold text-slate-600 mb-2">🔬 Ciência & Química</div>
                            <div class="grid grid-cols-5 gap-2 text-2xl text-center">
                                <button onclick="adicionarAdesivoTexto('⚗️')" class="p-2 bg-slate-50 hover:bg-brand-50 rounded-xl border border-slate-200 hover:border-brand-300 transition">⚗️</button>
                                <button onclick="adicionarAdesivoTexto('🧪')" class="p-2 bg-slate-50 hover:bg-brand-50 rounded-xl border border-slate-200 hover:border-brand-300 transition">🧪</button>
                                <button onclick="adicionarAdesivoTexto('⚛️')" class="p-2 bg-slate-50 hover:bg-brand-50 rounded-xl border border-slate-200 hover:border-brand-300 transition">⚛️</button>
                                <button onclick="adicionarAdesivoTexto('🔬')" class="p-2 bg-slate-50 hover:bg-brand-50 rounded-xl border border-slate-200 hover:border-brand-300 transition">🔬</button>
                                <button onclick="adicionarAdesivoTexto('🧬')" class="p-2 bg-slate-50 hover:bg-brand-50 rounded-xl border border-slate-200 hover:border-brand-300 transition">🧬</button>
                                <button onclick="adicionarAdesivoTexto('🔥')" class="p-2 bg-slate-50 hover:bg-brand-50 rounded-xl border border-slate-200 hover:border-brand-300 transition">🔥</button>
                                <button onclick="adicionarAdesivoTexto('💡')" class="p-2 bg-slate-50 hover:bg-brand-50 rounded-xl border border-slate-200 hover:border-brand-300 transition">💡</button>
                                <button onclick="adicionarAdesivoTexto('⚡')" class="p-2 bg-slate-50 hover:bg-brand-50 rounded-xl border border-slate-200 hover:border-brand-300 transition">⚡</button>
                                <button onclick="adicionarAdesivoTexto('🌡️')" class="p-2 bg-slate-50 hover:bg-brand-50 rounded-xl border border-slate-200 hover:border-brand-300 transition">🌡️</button>
                                <button onclick="adicionarAdesivoTexto('🪐')" class="p-2 bg-slate-50 hover:bg-brand-50 rounded-xl border border-slate-200 hover:border-brand-300 transition">🪐</button>
                            </div>
                        </div>

                        <div>
                            <div class="text-xs font-bold text-slate-600 mb-2">✨ Decorações do Mockup</div>
                            <div class="grid grid-cols-5 gap-2 text-2xl text-center">
                                <button onclick="adicionarAdesivoTexto('⭐')" class="p-2 bg-slate-50 hover:bg-brand-50 rounded-xl border border-slate-200 hover:border-brand-300 transition">⭐</button>
                                <button onclick="adicionarAdesivoTexto('💖')" class="p-2 bg-slate-50 hover:bg-brand-50 rounded-xl border border-slate-200 hover:border-brand-300 transition">💖</button>
                                <button onclick="adicionarAdesivoTexto('💜')" class="p-2 bg-slate-50 hover:bg-brand-50 rounded-xl border border-slate-200 hover:border-brand-300 transition">💜</button>
                                <button onclick="adicionarAdesivoTexto('😊')" class="p-2 bg-slate-50 hover:bg-brand-50 rounded-xl border border-slate-200 hover:border-brand-300 transition">😊</button>
                                <button onclick="adicionarAdesivoTexto('✨')" class="p-2 bg-slate-50 hover:bg-brand-50 rounded-xl border border-slate-200 hover:border-brand-300 transition">✨</button>
                                <button onclick="adicionarAdesivoTexto('🌟')" class="p-2 bg-slate-50 hover:bg-brand-50 rounded-xl border border-slate-200 hover:border-brand-300 transition">🌟</button>
                                <button onclick="adicionarAdesivoTexto('🌙')" class="p-2 bg-slate-50 hover:bg-brand-50 rounded-xl border border-slate-200 hover:border-brand-300 transition">🌙</button>
                                <button onclick="adicionarAdesivoTexto('🎈')" class="p-2 bg-slate-50 hover:bg-brand-50 rounded-xl border border-slate-200 hover:border-brand-300 transition">🎈</button>
                                <button onclick="adicionarAdesivoTexto('🏷️')" class="p-2 bg-slate-50 hover:bg-brand-50 rounded-xl border border-slate-200 hover:border-brand-300 transition">🏷️</button>
                                <button onclick="adicionarAdesivoTexto('🎯')" class="p-2 bg-slate-50 hover:bg-brand-50 rounded-xl border border-slate-200 hover:border-brand-300 transition">🎯</button>
                            </div>
                        </div>

                        <!-- Dica de arrastar -->
                        <div class="bg-brand-50 border border-brand-200 p-3 rounded-xl text-xs text-brand-800">
                            💡 Clique no adesivo para adicioná-lo e arraste-o livremente pela tela!
                        </div>
                    </div>
                </div>

                <!-- 4. ABA: FOTOS / IMAGENS -->
                <div id="aba-conteudo-fotos" class="hidden space-y-4">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-800 flex items-center gap-2">
                            <span>🖼️</span> Fotos e Imagens
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Envie imagens para colocar sobre a tabela</p>
                    </div>

                    <div class="border-2 border-dashed border-slate-300 hover:border-brand-500 rounded-2xl p-6 text-center cursor-pointer bg-slate-50 hover:bg-brand-50/30 transition"
                         onclick="document.getElementById('input-upload-foto').click()">
                        <input type="file" id="input-upload-foto" accept="image/*" class="hidden" onchange="uploadFoto(event)">
                        <span class="text-3xl block mb-2">📸</span>
                        <span class="text-xs font-bold text-slate-700 block">Clique para enviar imagem</span>
                        <span class="text-[10px] text-slate-400 mt-1 block">PNG, JPG, SVG ou WebP</span>
                    </div>

                    <div id="preview-fotos-inseridas" class="space-y-2">
                        <!-- Imagens adicionadas aparecem aqui -->
                    </div>
                </div>

                <!-- 5. ABA: LOGO DA EMPRESA -->
                <div id="aba-conteudo-logo" class="hidden space-y-4">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-800 flex items-center gap-2">
                            <span>🏢</span> Logo da Empresa ou Escola
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Insira a logomarca da sua instituição</p>
                    </div>

                    <div class="border-2 border-dashed border-slate-300 hover:border-brand-500 rounded-2xl p-5 text-center cursor-pointer bg-slate-50 transition"
                         onclick="document.getElementById('input-upload-logo').click()">
                        <input type="file" id="input-upload-logo" accept="image/*" class="hidden" onchange="uploadLogo(event)">
                        <span class="text-3xl block mb-2">🏫</span>
                        <span class="text-xs font-bold text-slate-700 block">Adicionar Logotipo</span>
                        <span class="text-[10px] text-slate-400 block mt-1">Ideal em PNG transparente</span>
                    </div>

                    <div id="opcoes-logo-painel" class="space-y-3 bg-slate-50 p-3.5 rounded-2xl border border-slate-200 hidden">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-700">Controles do Logo</span>
                            <button onclick="removerLogo()" class="text-xs text-rose-600 hover:underline">Remover</button>
                        </div>
                        
                        <div>
                            <div class="flex justify-between text-xs text-slate-600 mb-1">
                                <span>Tamanho do Logo:</span>
                                <span id="val-logo-size">120px</span>
                            </div>
                            <input type="range" min="40" max="300" value="120" oninput="ajustarLogoTamanho(this.value)" class="w-full">
                        </div>

                        <div>
                            <div class="flex justify-between text-xs text-slate-600 mb-1">
                                <span>Opacidade:</span>
                                <span id="val-logo-opacidade">100%</span>
                            </div>
                            <input type="range" min="10" max="100" value="100" oninput="ajustarLogoOpacidade(this.value)" class="w-full">
                        </div>
                    </div>
                </div>

                <!-- 6. ABA: FORMATO DOS QUADRINHOS -->
                <div id="aba-conteudo-quadrinhos" class="hidden space-y-5">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-800 flex items-center gap-2">
                            <span>🧩</span> Formato dos Quadrinhos
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Conforme as regras, o formato dos quadrinhos é 100% customizável!</p>
                    </div>

                    <!-- Cantos Arredondados -->
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-2">Cantos Arredondados (Border Radius)</label>
                        <div class="grid grid-cols-4 gap-2">
                            <button onclick="definirRadius('0px')" class="py-2 px-1 text-center bg-slate-100 hover:bg-brand-50 border border-slate-200 rounded-none text-xs font-bold">
                                Quadrado
                            </button>
                            <button onclick="definirRadius('8px')" class="py-2 px-1 text-center bg-slate-100 hover:bg-brand-50 border border-slate-200 rounded-lg text-xs font-bold">
                                Suave
                            </button>
                            <button onclick="definirRadius('16px')" class="py-2 px-1 text-center bg-slate-100 hover:bg-brand-50 border border-slate-200 rounded-2xl text-xs font-bold">
                                Redondo
                            </button>
                            <button onclick="definirRadius('9999px')" class="py-2 px-1 text-center bg-slate-100 hover:bg-brand-50 border border-slate-200 rounded-full text-xs font-bold">
                                Pílula
                            </button>
                        </div>
                    </div>

                    <!-- Estilo e Espessura da Borda -->
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-2">Espessura da Borda</label>
                        <div class="grid grid-cols-4 gap-2">
                            <button onclick="definirBordaWidth('1px')" class="py-1.5 bg-slate-100 hover:bg-brand-50 border border-slate-200 rounded-lg text-xs font-bold">Fina (1px)</button>
                            <button onclick="definirBordaWidth('2px')" class="py-1.5 bg-slate-100 hover:bg-brand-50 border border-slate-200 rounded-lg text-xs font-bold">Média (2px)</button>
                            <button onclick="definirBordaWidth('3px')" class="py-1.5 bg-slate-100 hover:bg-brand-50 border border-slate-200 rounded-lg text-xs font-bold">Grossa (3px)</button>
                            <button onclick="definirBordaWidth('0px')" class="py-1.5 bg-slate-100 hover:bg-brand-50 border border-slate-200 rounded-lg text-xs font-bold">Sem borda</button>
                        </div>
                    </div>

                    <!-- Sombras / Efeito 3D -->
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-2">Efeito de Sombra / Elevação</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button onclick="definirSombra('none')" class="py-2 bg-slate-100 hover:bg-brand-50 border border-slate-200 rounded-lg text-xs font-bold">Plano (Flat)</button>
                            <button onclick="definirSombra('0 4px 6px -1px rgba(0,0,0,0.1)')" class="py-2 bg-slate-100 hover:bg-brand-50 border border-slate-200 rounded-lg text-xs font-bold">Suave</button>
                            <button onclick="definirSombra('0 10px 15px -3px rgba(0,0,0,0.2)')" class="py-2 bg-slate-100 hover:bg-brand-50 border border-slate-200 rounded-lg text-xs font-bold">3D Intenso</button>
                        </div>
                    </div>

                    <!-- Nível de Detalhe -->
                    <div>
                        <label class="text-xs font-bold text-slate-700 block mb-2">Informações Exibidas nos Cards</label>
                        <div class="space-y-1.5 text-xs text-slate-600">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" id="chk-mostrar-massa" checked onchange="toggleDetalhe('massa', this.checked)" class="rounded text-brand-600">
                                <span>Mostrar Massa Atômica (5 algarismos)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" id="chk-mostrar-nome" checked onchange="toggleDetalhe('nome', this.checked)" class="rounded text-brand-600">
                                <span>Mostrar Nome em Português</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- 7. ABA: FUNDOS -->
                <div id="aba-conteudo-fundos" class="hidden space-y-4">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-800 flex items-center gap-2">
                            <span>🌄</span> Planos de Fundo
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Altere o fundo do painel da tabela</p>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5">
                        <button onclick="definirFundo('branco')" class="p-2.5 rounded-xl border border-slate-200 bg-white hover:border-brand-400 text-left transition">
                            <div class="w-full h-8 rounded-lg bg-white border border-slate-200 mb-1.5"></div>
                            <span class="text-xs font-bold text-slate-700">Branco Puro</span>
                        </button>

                        <button onclick="definirFundo('lavanda')" class="p-2.5 rounded-xl border border-slate-200 bg-brand-50/40 hover:border-brand-400 text-left transition">
                            <div class="w-full h-8 rounded-lg bg-gradient-to-br from-[#eae8f8] to-[#f4f3fb] border border-brand-200 mb-1.5"></div>
                            <span class="text-xs font-bold text-slate-700">Lavanda Pastel</span>
                        </button>

                        <button onclick="definirFundo('montanha')" class="p-2.5 rounded-xl border border-slate-200 hover:border-brand-400 text-left transition">
                            <div class="w-full h-8 rounded-lg bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-400 mb-1.5"></div>
                            <span class="text-xs font-bold text-slate-700">Pôr do Sol (Mockup)</span>
                        </button>

                        <button onclick="definirFundo('galaxia')" class="p-2.5 rounded-xl border border-slate-700 bg-slate-900 text-left transition">
                            <div class="w-full h-8 rounded-lg bg-gradient-to-r from-slate-950 via-purple-950 to-slate-900 mb-1.5"></div>
                            <span class="text-xs font-bold text-white">Galáxia / Estrelas</span>
                        </button>
                    </div>

                    <div class="pt-2">
                        <div class="flex items-center justify-between text-xs mb-2">
                            <span class="text-slate-600 font-bold">Cor de Fundo Sólida:</span>
                            <input type="color" id="cor-fundo-custom" value="#ffffff" onchange="definirFundoCor(this.value)" class="w-8 h-8 rounded-lg cursor-pointer">
                        </div>
                    </div>
                </div>

                <!-- 8. ABA: TAMANHO E ZOOM -->
                <div id="aba-conteudo-tamanho" class="hidden space-y-5">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-800 flex items-center gap-2">
                            <span>📐</span> Ajuste de Tamanho e Zoom
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Sem alterar as informações da tabela periódica</p>
                    </div>

                    <div>
                        <div class="flex justify-between text-xs text-slate-700 font-bold mb-1.5">
                            <span>Zoom da Tabela:</span>
                            <span id="val-zoom">100%</span>
                        </div>
                        <input type="range" min="50" max="150" value="100" oninput="ajustarZoom(this.value)" class="w-full">
                        <div class="flex justify-between text-[10px] text-slate-400 mt-1">
                            <span>50%</span>
                            <span>100%</span>
                            <span>150%</span>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-xs text-slate-700 font-bold mb-1.5">
                            <span>Espaçamento entre Células:</span>
                            <span id="val-gap">4px</span>
                        </div>
                        <input type="range" min="1" max="12" value="4" oninput="ajustarGap(this.value)" class="w-full">
                    </div>

                    <div>
                        <div class="flex justify-between text-xs text-slate-700 font-bold mb-1.5">
                            <span>Espaço Interno (Padding):</span>
                            <span id="val-padding">4px</span>
                        </div>
                        <input type="range" min="1" max="8" value="4" oninput="ajustarPadding(this.value)" class="w-full">
                    </div>
                </div>

            </div>

            <!-- Botão de Fechar Sidebar Mobile -->
            <div class="p-3 border-t border-slate-200 lg:hidden text-center">
                <button onclick="toggleMobileSidebar()" class="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">
                    Fechar Painel
                </button>
            </div>
        </aside>

        <!-- Center Canvas: Área de Visualização e Decoração da Tabela Periódica -->
        <main class="flex-1 overflow-auto p-3 sm:p-6 flex flex-col justify-between items-center relative">
            
            <!-- Banner do Projeto -->
            <div class="w-full max-w-6xl flex items-center justify-between mb-3 text-xs sm:text-sm text-slate-500">
                <div class="flex items-center gap-2 font-bold text-slate-700">
                    <span>💜</span>
                    <span id="banner-titulo-preview">Sua tabela periódica</span>
                </div>
                <div class="flex items-center gap-3 text-xs">
                    <span class="bg-brand-50 text-brand-700 font-semibold px-2.5 py-1 rounded-full border border-brand-200">
                        118 Elementos Oficiais
                    </span>
                    <span class="text-slate-400 hidden sm:inline">Vetorizado SVG/CSS</span>
                </div>
            </div>

            <!-- Moldura do Canvas da Tabela (Onde as decorações acontecem) -->
            <div id="canvas-tabela-container" 
                 class="w-full max-w-6xl bg-white rounded-3xl border border-slate-200/90 shadow-xl shadow-brand-500/5 p-4 sm:p-8 relative transition-all duration-300"
                 style="min-height: 540px;">
                
                <!-- Logo da Empresa Flutuante (se adicionado) -->
                <div id="elemento-logo-empresa" class="item-decorativo hidden z-30" style="top: 24px; left: 24px;">
                    <img id="img-logo-empresa" src="" alt="Logo da Empresa" class="max-h-24 object-contain pointer-events-none">
                </div>

                <!-- Título Personalizado na Tabela -->
                <div class="text-center mb-6 pointer-events-none select-none">
                    <h2 id="titulo-tabela-render" class="text-xl sm:text-2xl font-black text-slate-800 tracking-tight">
                        TABELA PERIÓDICA DOS ELEMENTOS
                    </h2>
                    <p id="subtitulo-tabela-render" class="text-xs text-slate-500 font-medium">
                        IUPAC - Pesos atômicos com 5 algarismos significativos
                    </p>
                </div>

                <!-- Container do Grid com Escala Vetorial -->
                <div id="wrapper-tabela" class="w-full transition-transform duration-200 origin-top">
                    <!-- Grid da Tabela Periódica de 118 Elementos -->
                    <div id="grid-elementos" class="tabela-grid">
                        <!-- Gerado dinamicamente com os 118 elementos -->
                    </div>

                    <!-- Espaçamento para Lantanídeos e Actinídeos destacados embaixo -->
                    <div class="mt-6 pt-4 border-t border-dashed border-slate-200">
                        <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest text-center mb-2">
                            Séries de Transição Interna (Lantanídeos & Actinídeos)
                        </div>
                        <div id="grid-lantanideos-actinideos" class="tabela-grid">
                            <!-- Inseridos dinamicamente rows 9 e 10 -->
                        </div>
                    </div>
                </div>

                <!-- Camada de Adesivos e Decorações Flutuantes -->
                <div id="camada-decorativa" class="absolute inset-0 pointer-events-none overflow-hidden rounded-3xl">
                    <!-- Adesivos inseridos dinamicamente têm pointer-events-auto -->
                </div>

            </div>

            <!-- Barra Inferior de Ações (Exatamente como Tela 2 do Mockup) -->
            <div class="w-full max-w-6xl mt-4 flex flex-col sm:flex-row items-center justify-between gap-3 bg-white border border-slate-200/90 rounded-2xl p-3 px-5 shadow-sm">
                
                <!-- Dica Educativa -->
                <div class="flex items-center gap-2 text-xs text-slate-500">
                    <span class="text-base text-amber-500">💡</span>
                    <span><strong>Dica:</strong> use as ferramentas ao lado para personalizar sua tabela!</span>
                </div>

                <!-- Botões de Ação do Rodapé -->
                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <button onclick="desfazerAcao()" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                        <span>↶</span> Desfazer
                    </button>
                    <button onclick="limparDecoracoes()" class="px-3.5 py-2 bg-slate-100 hover:bg-rose-50 text-slate-700 hover:text-rose-600 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                        <span>🗑️</span> Limpar
                    </button>
                    <button onclick="alternarModoFoco()" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                        <span>👁️</span> Visualizar
                    </button>
                    <button onclick="finalizarProjeto()" class="px-5 py-2 bg-brand-600 hover:bg-brand-700 active:scale-95 text-white rounded-xl text-xs font-extrabold transition shadow-md shadow-brand-600/25 flex items-center gap-1.5">
                        <span>✔️</span> Finalizar
                    </button>
                </div>

            </div>

        </main>

    </div>

    <!-- Modal de Detalhes do Elemento Químico -->
    <div id="modal-elemento" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 relative animate-in fade-in zoom-in duration-200">
            <button onclick="fecharModalElemento()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-700 text-xl font-bold w-8 h-8 rounded-full flex items-center justify-center bg-slate-100">
                ✕
            </button>
            <div id="modal-elemento-conteudo">
                <!-- Preenchido via JS ao clicar em qualquer elemento -->
            </div>
        </div>
    </div>

    <!-- Script com Dados dos 118 Elementos e Motor de Personalização -->
    <script src="js_elementos.js"></script>
    <script>
        // Estado Global da Personalização da Tabela Periódica
        const ESTADO_PADRAO = {
            id: <?= $projetoId ?>,
            titulo: "<?= addslashes($projetoTitulo) ?>",
            zoom: 100,
            gap: 4,
            padding: 4,
            borderRadius: '8px',
            borderWidth: '1.5px',
            corTexto: '#1e293b',
            corBorda: '#cbd5e1',
            sombra: '0 1px 2px rgba(0,0,0,0.05)',
            fundoTipo: 'branco',
            fundoCor: '#ffffff',
            mostrarMassa: true,
            mostrarNome: true,
            logo: {
                ativo: false,
                src: '',
                largura: 120,
                opacidade: 100,
                x: 24,
                y: 24
            },
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
            elementosCoresCustom: {}, // Cor individual sobrescrita
            adesivos: []
        };

        // Carregar estado salvo do banco caso exista
        let estadoCarregado = <?= $projetoConfigJson ?>;
        let estado = (estadoCarregado && typeof estadoCarregado === 'object') 
                     ? Object.assign({}, ESTADO_PADRAO, estadoCarregado) 
                     : JSON.parse(JSON.stringify(ESTADO_PADRAO));

        // Pilha de Histórico para Desfazer / Refazer
        let historico = [JSON.stringify(estado)];
        let indiceHistorico = 0;

        const NOMES_FAMILIAS = {
            'nao-metal': 'Não metais',
            'gas-nobre': 'Gases nobres',
            'metal-alcalino': 'Metais alcalinos',
            'alcalino-terroso': 'Alcalino-terrosos',
            'semimetal': 'Semimetais',
            'halogenio': 'Halogênios',
            'metal-transicao': 'Metais de transição',
            'pos-transicao': 'Pós-transição',
            'lantanideo': 'Lantanídeos',
            'actinideo': 'Actinídeos'
        };

        // Inicialização
        document.addEventListener('DOMContentLoaded', () => {
            renderizarListaCoresFamilias();
            renderizarTabela();
            aplicarEstadoVisual();
            configurarInputTitulo();
            restaurarAdesivos();
        });

        function configurarInputTitulo() {
            const input = document.getElementById('projeto-nome');
            input.addEventListener('input', (e) => {
                estado.titulo = e.target.value.trim() || "Projeto sem nome";
                document.getElementById('banner-titulo-preview').innerText = estado.titulo;
            });
        }

        // Renderiza o grid dos 118 elementos na disposição padrão da tabela
        function renderizarTabela() {
            const gridPrincipal = document.getElementById('grid-elementos');
            const gridLantanideos = document.getElementById('grid-lantanideos-actinideos');
            gridPrincipal.innerHTML = '';
            gridLantanideos.innerHTML = '';

            // Dicionário de elementos por posição row/col
            const mapaPosicoes = {};
            window.TABELA_ELEMENTOS.forEach(el => {
                mapaPosicoes[`${el.row}_${el.col}`] = el;
            });

            // Grid Principal: 7 períodos (linhas 1 a 7) e 18 grupos (colunas 1 a 18)
            for (let r = 1; r <= 7; r++) {
                for (let c = 1; c <= 18; c++) {
                    const el = mapaPosicoes[`${r}_${c}`];
                    if (el) {
                        gridPrincipal.appendChild(criarCardElemento(el));
                    } else if (r === 6 && c === 3) {
                        // Indicador de Lantanídeos 57-71
                        const placeholder = document.createElement('div');
                        placeholder.className = 'elemento-card flex items-center justify-center text-center font-bold text-[7px] sm:text-[9px] bg-purple-50 text-purple-700 border-purple-200 cursor-pointer';
                        placeholder.innerHTML = '<span>57-71<br>La-Lu</span>';
                        placeholder.onclick = () => document.getElementById('grid-lantanideos-actinideos').scrollIntoView({behavior: 'smooth'});
                        gridPrincipal.appendChild(placeholder);
                    } else if (r === 7 && c === 3) {
                        // Indicador de Actinídeos 89-103
                        const placeholder = document.createElement('div');
                        placeholder.className = 'elemento-card flex items-center justify-center text-center font-bold text-[7px] sm:text-[9px] bg-fuchsia-50 text-fuchsia-700 border-fuchsia-200 cursor-pointer';
                        placeholder.innerHTML = '<span>89-103<br>Ac-Lr</span>';
                        placeholder.onclick = () => document.getElementById('grid-lantanideos-actinideos').scrollIntoView({behavior: 'smooth'});
                        gridPrincipal.appendChild(placeholder);
                    } else {
                        // Célula Vazia
                        const vazio = document.createElement('div');
                        vazio.className = 'pointer-events-none';
                        gridPrincipal.appendChild(vazio);
                    }
                }
            }

            // Grid Lantanídeos (linha 9) e Actinídeos (linha 10)
            for (let r = 9; r <= 10; r++) {
                // Espaço de alinhamento para colunas 1 e 2
                const esp1 = document.createElement('div');
                const esp2 = document.createElement('div');
                gridLantanideos.appendChild(esp1);
                gridLantanideos.appendChild(esp2);

                for (let c = 3; c <= 17; c++) {
                    const el = mapaPosicoes[`${r}_${c}`];
                    if (el) {
                        gridLantanideos.appendChild(criarCardElemento(el));
                    } else {
                        gridLantanideos.appendChild(document.createElement('div'));
                    }
                }

                // Espaço coluna 18
                gridLantanideos.appendChild(document.createElement('div'));
            }
        }

        // Cria o card de cada elemento químico com informações oficiais intocadas
        function criarCardElemento(el) {
            const card = document.createElement('div');
            card.className = 'elemento-card';
            card.id = `el-card-${el.numero}`;
            card.dataset.numero = el.numero;
            card.dataset.categoria = el.categoria;

            // Cor de fundo conforme família ou override
            const corBg = estado.elementosCoresCustom[el.numero] || estado.coresFamilias[el.categoria] || '#e2e8f0';
            card.style.backgroundColor = corBg;
            card.style.borderColor = estado.corBorda;
            card.style.color = estado.corTexto;

            card.innerHTML = `
                <div class="flex items-center justify-between text-[6px] sm:text-[8px] font-mono leading-none">
                    <span class="font-bold opacity-80">${el.numero}</span>
                    <span class="el-massa text-[5px] sm:text-[7px] opacity-70 ${estado.mostrarMassa ? '' : 'hidden'}">${el.massa}</span>
                </div>
                <div class="text-center font-black text-xs sm:text-sm lg:text-base tracking-tight leading-tight my-auto">
                    ${el.simbolo}
                </div>
                <div class="el-nome text-center text-[5px] sm:text-[7px] truncate font-semibold leading-none opacity-90 ${estado.mostrarNome ? '' : 'hidden'}">
                    ${el.nome}
                </div>
            `;

            card.onclick = () => abrirModalElemento(el);
            return card;
        }

        // Modal com detalhes oficiais e opção de cor individual
        function abrirModalElemento(el) {
            const modal = document.getElementById('modal-elemento');
            const conteudo = document.getElementById('modal-elemento-conteudo');
            const corAtual = estado.elementosCoresCustom[el.numero] || estado.coresFamilias[el.categoria];

            conteudo.innerHTML = `
                <div class="flex items-start gap-4 mb-4">
                    <div class="w-20 h-24 rounded-2xl flex flex-col justify-between p-2 text-center border-2 border-slate-300 shadow-md font-sans"
                         style="background-color: ${corAtual}; color: ${estado.corTexto};">
                        <span class="text-xs font-mono font-bold text-left opacity-80">${el.numero}</span>
                        <span class="text-2xl font-black">${el.simbolo}</span>
                        <span class="text-[9px] font-bold truncate">${el.nome}</span>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-brand-600 uppercase tracking-wider">${NOMES_FAMILIAS[el.categoria] || el.categoria}</div>
                        <h3 class="text-2xl font-black text-slate-800">${el.nome}</h3>
                        <p class="text-xs text-slate-500 mt-1">Número atômico: <strong class="text-slate-700">${el.numero}</strong> | Massa oficial: <strong class="text-slate-700">${el.massa} u</strong></p>
                        <p class="text-xs text-slate-500">Período: <strong class="text-slate-700">${el.periodo}</strong> | Grupo: <strong class="text-slate-700">${el.grupo}</strong></p>
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-4 space-y-3">
                    <label class="text-xs font-bold text-slate-700 block">Personalizar Cor Individual Deste Elemento:</label>
                    <div class="flex items-center gap-3">
                        <input type="color" id="modal-cor-picker" value="${corAtual}" 
                               class="w-10 h-10 rounded-xl cursor-pointer border border-slate-300"
                               onchange="alterarCorIndividual(${el.numero}, this.value)">
                        <span class="text-xs text-slate-500">Clique para escolher uma cor exclusiva</span>
                        <button onclick="removerCorIndividual(${el.numero})" class="ml-auto text-xs text-rose-600 hover:underline">
                            Restaurar padrão
                        </button>
                    </div>
                </div>
            `;
            modal.classList.remove('hidden');
        }

        function fecharModalElemento() {
            document.getElementById('modal-elemento').classList.add('hidden');
        }

        function alterarCorIndividual(num, cor) {
            estado.elementosCoresCustom[num] = cor;
            const card = document.getElementById(`el-card-${num}`);
            if (card) card.style.backgroundColor = cor;
            salvarHistorico();
        }

        function removerCorIndividual(num) {
            delete estado.elementosCoresCustom[num];
            const el = window.TABELA_ELEMENTOS.find(e => e.numero === num);
            if (el) {
                const card = document.getElementById(`el-card-${num}`);
                if (card) card.style.backgroundColor = estado.coresFamilias[el.categoria];
            }
            fecharModalElemento();
            salvarHistorico();
        }

        // Renderiza lista de cores por família química na sidebar
        function renderizarListaCoresFamilias() {
            const container = document.getElementById('lista-cores-familias');
            container.innerHTML = '';

            for (const [chave, nome] of Object.entries(NOMES_FAMILIAS)) {
                const cor = estado.coresFamilias[chave] || '#cbd5e1';
                const row = document.createElement('div');
                row.className = 'flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-200/60';
                row.innerHTML = `
                    <span class="font-medium text-slate-700">${nome}</span>
                    <div class="flex items-center gap-2">
                        <input type="color" value="${cor}" onchange="atualizarCorFamilia('${chave}', this.value)"
                               class="w-7 h-7 rounded-lg cursor-pointer border border-slate-300">
                    </div>
                `;
                container.appendChild(row);
            }
        }

        function atualizarCorFamilia(familia, cor) {
            estado.coresFamilias[familia] = cor;
            // Atualiza todos os cards daquela família que não possuem cor customizada individual
            document.querySelectorAll(`.elemento-card[data-categoria="${familia}"]`).forEach(card => {
                const num = parseInt(card.dataset.numero);
                if (!estado.elementosCoresCustom[num]) {
                    card.style.backgroundColor = cor;
                }
            });
            salvarHistorico();
        }

        // Aplicação de Estilos e Formato dos Quadrinhos
        function atualizarEstilo(prop, valor) {
            estado[prop] = valor;
            if (prop === 'corTexto') {
                document.querySelectorAll('.elemento-card').forEach(c => c.style.color = valor);
            } else if (prop === 'corBorda') {
                document.querySelectorAll('.elemento-card').forEach(c => c.style.borderColor = valor);
            }
            salvarHistorico();
        }

        function definirRadius(val) {
            estado.borderRadius = val;
            document.documentElement.style.setProperty('--card-radius', val);
            salvarHistorico();
        }

        function definirBordaWidth(val) {
            estado.borderWidth = val;
            document.documentElement.style.setProperty('--card-border-width', val);
            salvarHistorico();
        }

        function definirSombra(val) {
            estado.sombra = val;
            document.documentElement.style.setProperty('--card-shadow', val);
            salvarHistorico();
        }

        function toggleDetalhe(tipo, mostrar) {
            if (tipo === 'massa') {
                estado.mostrarMassa = mostrar;
                document.querySelectorAll('.el-massa').forEach(el => el.classList.toggle('hidden', !mostrar));
            } else if (tipo === 'nome') {
                estado.mostrarNome = mostrar;
                document.querySelectorAll('.el-nome').forEach(el => el.classList.toggle('hidden', !mostrar));
            }
            salvarHistorico();
        }

        // Planos de Fundo
        function definirFundo(tipo) {
            estado.fundoTipo = tipo;
            const container = document.getElementById('canvas-tabela-container');
            container.className = "w-full max-w-6xl rounded-3xl border border-slate-200/90 shadow-xl p-4 sm:p-8 relative transition-all duration-300";

            if (tipo === 'branco') {
                container.style.background = '#ffffff';
                container.style.color = '#1e293b';
            } else if (tipo === 'lavanda') {
                container.style.background = 'linear-gradient(135deg, #f4f3fb 0%, #eae8f8 100%)';
            } else if (tipo === 'montanha') {
                // Fundo com pôr do sol como no mockup da Tela 3!
                container.style.background = 'linear-gradient(180deg, #fbcfe8 0%, #c4b5fd 40%, #818cf8 100%)';
            } else if (tipo === 'galaxia') {
                container.style.background = 'linear-gradient(135deg, #020617 0%, #1e1b4b 50%, #0f172a 100%)';
            }
            salvarHistorico();
        }

        function definirFundoCor(cor) {
            estado.fundoTipo = 'custom';
            estado.fundoCor = cor;
            document.getElementById('canvas-tabela-container').style.background = cor;
            salvarHistorico();
        }

        // Controles de Tamanho e Zoom
        function ajustarZoom(val) {
            estado.zoom = parseInt(val);
            document.getElementById('val-zoom').innerText = `${val}%`;
            document.getElementById('wrapper-tabela').style.transform = `scale(${val / 100})`;
        }

        function ajustarGap(val) {
            estado.gap = parseInt(val);
            document.getElementById('val-gap').innerText = `${val}px`;
            document.documentElement.style.setProperty('--element-gap', `${val}px`);
        }

        function ajustarPadding(val) {
            estado.padding = parseInt(val);
            document.getElementById('val-padding').innerText = `${val}px`;
            document.documentElement.style.setProperty('--card-padding', `${val}px`);
        }

        // Aplicação de Templates Prontos
        function aplicarTemplate(nome) {
            if (nome === 'pastel') {
                // Idêntico ao mockup
                estado.coresFamilias = {
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
                };
                estado.corTexto = '#1e293b';
                estado.corBorda = '#cbd5e1';
                estado.borderRadius = '8px';
                definirFundo('lavanda');
            } else if (nome === 'classico') {
                estado.coresFamilias = {
                    'nao-metal': '#a7f3d0',
                    'gas-nobre': '#c4b5fd',
                    'metal-alcalino': '#fca5a5',
                    'alcalino-terroso': '#fdba74',
                    'semimetal': '#6ee7b7',
                    'halogenio': '#67e8f9',
                    'metal-transicao': '#93c5fd',
                    'pos-transicao': '#7dd3fc',
                    'lantanideo': '#d8b4fe',
                    'actinideo': '#f0abfc'
                };
                estado.corTexto = '#0f172a';
                estado.corBorda = '#94a3b8';
                estado.borderRadius = '4px';
                definirFundo('branco');
            } else if (nome === 'cyberpunk') {
                estado.coresFamilias = {
                    'nao-metal': '#f43f5e',
                    'gas-nobre': '#d946ef',
                    'metal-alcalino': '#f59e0b',
                    'alcalino-terroso': '#eab308',
                    'semimetal': '#10b981',
                    'halogenio': '#06b6d4',
                    'metal-transicao': '#3b82f6',
                    'pos-transicao': '#0ea5e9',
                    'lantanideo': '#8b5cf6',
                    'actinideo': '#ec4899'
                };
                estado.corTexto = '#ffffff';
                estado.corBorda = '#475569';
                estado.borderRadius = '6px';
                definirFundo('galaxia');
            } else if (nome === 'minimalista') {
                estado.coresFamilias = {
                    'nao-metal': '#f8fafc',
                    'gas-nobre': '#e2e8f0',
                    'metal-alcalino': '#f1f5f9',
                    'alcalino-terroso': '#e2e8f0',
                    'semimetal': '#f8fafc',
                    'halogenio': '#e2e8f0',
                    'metal-transicao': '#cbd5e1',
                    'pos-transicao': '#f1f5f9',
                    'lantanideo': '#e2e8f0',
                    'actinideo': '#cbd5e1'
                };
                estado.corTexto = '#000000';
                estado.corBorda = '#000000';
                estado.borderRadius = '0px';
                definirFundo('branco');
            } else if (nome === 'sunset') {
                estado.coresFamilias = {
                    'nao-metal': '#fb7185',
                    'gas-nobre': '#c084fc',
                    'metal-alcalino': '#fb923c',
                    'alcalino-terroso': '#facc15',
                    'semimetal': '#4ade80',
                    'halogenio': '#38bdf8',
                    'metal-transicao': '#f472b6',
                    'pos-transicao': '#f87171',
                    'lantanideo': '#a855f7',
                    'actinideo': '#e879f9'
                };
                estado.corTexto = '#1c1917';
                estado.corBorda = '#f43f5e';
                estado.borderRadius = '12px';
                definirFundo('montanha');
            } else if (nome === 'educacional') {
                estado.coresFamilias = {
                    'nao-metal': '#38bdf8',
                    'gas-nobre': '#a855f7',
                    'metal-alcalino': '#f87171',
                    'alcalino-terroso': '#fb923c',
                    'semimetal': '#4ade80',
                    'halogenio': '#22d3ee',
                    'metal-transicao': '#facc15',
                    'pos-transicao': '#60a5fa',
                    'lantanideo': '#c084fc',
                    'actinideo': '#f472b6'
                };
                estado.corTexto = '#0f172a';
                estado.corBorda = '#e2e8f0';
                estado.borderRadius = '14px';
                definirFundo('lavanda');
            }

            aplicarEstadoVisual();
            salvarHistorico();
        }

        // Aplica o estado visual em todas as propriedades CSS
        function aplicarEstadoVisual() {
            document.documentElement.style.setProperty('--card-radius', estado.borderRadius);
            document.documentElement.style.setProperty('--card-border-width', estado.borderWidth);
            document.documentElement.style.setProperty('--card-shadow', estado.sombra);
            document.documentElement.style.setProperty('--element-gap', `${estado.gap}px`);
            document.documentElement.style.setProperty('--card-padding', `${estado.padding}px`);

            renderizarListaCoresFamilias();

            // Atualiza cada card
            document.querySelectorAll('.elemento-card').forEach(card => {
                const num = parseInt(card.dataset.numero);
                const cat = card.dataset.categoria;
                card.style.backgroundColor = estado.elementosCoresCustom[num] || estado.coresFamilias[cat] || '#cbd5e1';
                card.style.borderColor = estado.corBorda;
                card.style.color = estado.corTexto;
            });

            definirFundo(estado.fundoTipo);
        }

        // Sistema de Adesivos e Drag-and-Drop
        function adicionarAdesivoTexto(simbolo) {
            const id = 'adesivo_' + Date.now();
            const adesivo = {
                id: id,
                tipo: 'emoji',
                conteudo: simbolo,
                x: 100 + Math.random() * 200,
                y: 60 + Math.random() * 150,
                tamanho: 36
            };
            estado.adesivos.push(adesivo);
            renderizarAdesivoNaTela(adesivo);
            salvarHistorico();
        }

        function renderizarAdesivoNaTela(item) {
            const camada = document.getElementById('camada-decorativa');
            const el = document.createElement('div');
            el.id = item.id;
            el.className = 'item-decorativo pointer-events-auto text-center flex items-center justify-center';
            el.style.left = `${item.x}px`;
            el.style.top = `${item.y}px`;
            el.style.fontSize = `${item.tamanho}px`;
            el.style.zIndex = 35;

            if (item.tipo === 'imagem') {
                el.innerHTML = `<img src="${item.conteudo}" class="w-full h-full object-contain pointer-events-none select-none">`;
                el.style.width = `${item.tamanho}px`;
                el.style.height = `${item.tamanho}px`;
            } else {
                el.innerText = item.conteudo;
            }

            // Tornar arrastável (Mouse e Touch)
            tornarArrastavel(el, item);

            // Menu de clique duplo para remover
            el.ondblclick = () => {
                removerAdesivo(item.id);
            };

            camada.appendChild(el);
        }

        function tornarArrastavel(elemento, dadosItem) {
            let inicioX = 0, inicioY = 0, posAtualX = dadosItem.x, posAtualY = dadosItem.y;

            elemento.onmousedown = arrastarMouseDown;
            elemento.ontouchstart = arrastarTouchStart;

            function arrastarMouseDown(e) {
                e.preventDefault();
                inicioX = e.clientX;
                inicioY = e.clientY;
                document.onmouseup = fecharArrastar;
                document.onmousemove = arrastarMouse;
            }

            function arrastarMouse(e) {
                e.preventDefault();
                const deltaX = e.clientX - inicioX;
                const deltaY = e.clientY - inicioY;
                inicioX = e.clientX;
                inicioY = e.clientY;
                posAtualX += deltaX;
                posAtualY += deltaY;
                elemento.style.left = `${posAtualX}px`;
                elemento.style.top = `${posAtualY}px`;
            }

            function arrastarTouchStart(e) {
                const touch = e.touches[0];
                inicioX = touch.clientX;
                inicioY = touch.clientY;
                document.ontouchend = fecharArrastar;
                document.ontouchmove = arrastarTouch;
            }

            function arrastarTouch(e) {
                const touch = e.touches[0];
                const deltaX = touch.clientX - inicioX;
                const deltaY = touch.clientY - inicioY;
                inicioX = touch.clientX;
                inicioY = touch.clientY;
                posAtualX += deltaX;
                posAtualY += deltaY;
                elemento.style.left = `${posAtualX}px`;
                elemento.style.top = `${posAtualY}px`;
            }

            function fecharArrastar() {
                document.onmouseup = null;
                document.onmousemove = null;
                document.ontouchend = null;
                document.ontouchmove = null;
                dadosItem.x = posAtualX;
                dadosItem.y = posAtualY;
                salvarHistorico();
            }
        }

        function removerAdesivo(id) {
            estado.adesivos = estado.adesivos.filter(a => a.id !== id);
            const el = document.getElementById(id);
            if (el) el.remove();
            salvarHistorico();
        }

        function restaurarAdesivos() {
            const camada = document.getElementById('camada-decorativa');
            camada.innerHTML = '';
            if (Array.isArray(estado.adesivos)) {
                estado.adesivos.forEach(renderizarAdesivoNaTela);
            }
            if (estado.logo && estado.logo.ativo && estado.logo.src) {
                const logoEl = document.getElementById('elemento-logo-empresa');
                const img = document.getElementById('img-logo-empresa');
                img.src = estado.logo.src;
                logoEl.style.left = `${estado.logo.x}px`;
                logoEl.style.top = `${estado.logo.y}px`;
                logoEl.classList.remove('hidden');
                document.getElementById('opcoes-logo-painel').classList.remove('hidden');
                tornarArrastavel(logoEl, estado.logo);
            }
        }

        function limparDecoracoes() {
            if (confirm("Deseja remover todos os adesivos e decorações da tela?")) {
                estado.adesivos = [];
                document.getElementById('camada-decorativa').innerHTML = '';
                salvarHistorico();
            }
        }

        // Upload de Imagens e Logotipo
        function uploadFoto(e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(evt) {
                const adesivo = {
                    id: 'foto_' + Date.now(),
                    tipo: 'imagem',
                    conteudo: evt.target.result,
                    x: 120,
                    y: 80,
                    tamanho: 80
                };
                estado.adesivos.push(adesivo);
                renderizarAdesivoNaTela(adesivo);
                salvarHistorico();
            };
            reader.readAsDataURL(file);
        }

        function uploadLogo(e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(evt) {
                estado.logo.ativo = true;
                estado.logo.src = evt.target.result;
                const logoEl = document.getElementById('elemento-logo-empresa');
                const img = document.getElementById('img-logo-empresa');
                img.src = evt.target.result;
                logoEl.classList.remove('hidden');
                document.getElementById('opcoes-logo-painel').classList.remove('hidden');
                tornarArrastavel(logoEl, estado.logo);
                salvarHistorico();
            };
            reader.readAsDataURL(file);
        }

        function ajustarLogoTamanho(val) {
            estado.logo.largura = parseInt(val);
            document.getElementById('val-logo-size').innerText = `${val}px`;
            document.getElementById('img-logo-empresa').style.maxWidth = `${val}px`;
        }

        function ajustarLogoOpacidade(val) {
            estado.logo.opacidade = parseInt(val);
            document.getElementById('val-logo-opacidade').innerText = `${val}%`;
            document.getElementById('img-logo-empresa').style.opacity = val / 100;
        }

        function removerLogo() {
            estado.logo.ativo = false;
            estado.logo.src = '';
            document.getElementById('elemento-logo-empresa').classList.add('hidden');
            document.getElementById('opcoes-logo-painel').classList.add('hidden');
            salvarHistorico();
        }

        // Histórico de Desfazer e Refazer
        function salvarHistorico() {
            const estadoStr = JSON.stringify(estado);
            if (historico[indiceHistorico] !== estadoStr) {
                historico = historico.slice(0, indiceHistorico + 1);
                historico.push(estadoStr);
                indiceHistorico++;
            }
        }

        function desfazerAcao() {
            if (indiceHistorico > 0) {
                indiceHistorico--;
                estado = JSON.parse(historico[indiceHistorico]);
                aplicarEstadoVisual();
                restaurarAdesivos();
            }
        }

        function refazerAcao() {
            if (indiceHistorico < historico.length - 1) {
                indiceHistorico++;
                estado = JSON.parse(historico[indiceHistorico]);
                aplicarEstadoVisual();
                restaurarAdesivos();
            }
        }

        // Abas da Sidebar
        function abrirAbaSidebar(aba) {
            const abas = ['modelos', 'cores', 'adesivos', 'fotos', 'logo', 'quadrinhos', 'fundos', 'tamanho'];
            abas.forEach(a => {
                const conteudo = document.getElementById(`aba-conteudo-${a}`);
                const btn = document.getElementById(`tab-btn-${a}`);
                if (conteudo && btn) {
                    if (a === aba) {
                        conteudo.classList.remove('hidden');
                        btn.className = "tab-sidebar-btn px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1 bg-white shadow-sm text-brand-600";
                    } else {
                        conteudo.classList.add('hidden');
                        btn.className = "tab-sidebar-btn px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1 text-slate-600 hover:text-slate-900";
                    }
                }
            });
        }

        function toggleMobileSidebar() {
            const sidebar = document.getElementById('sidebar-ferramentas');
            sidebar.classList.toggle('-translate-x-full');
        }

        function alternarModoFoco() {
            const sidebar = document.getElementById('sidebar-ferramentas');
            sidebar.classList.toggle('lg:hidden');
        }

        // SALVAR PROJETO NO BANCO DE DADOS (MySQL com PDO)
        async function salvarProjetoNoBanco(silencioso = false) {
            const btnSalvar = document.getElementById('btn-salvar');
            const btnTexto = document.getElementById('btn-salvar-texto');
            
            if (!silencioso) {
                btnTexto.innerText = "Salvando...";
                btnSalvar.classList.add('opacity-75');
            }

            try {
                const resposta = await fetch('salvar_projeto.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id: estado.id,
                        titulo: estado.titulo,
                        config_json: JSON.stringify(estado)
                    })
                });

                const resultado = await resposta.json();
                if (resultado.sucesso) {
                    estado.id = resultado.id;
                    if (!silencioso) {
                        btnTexto.innerText = "Salvo! ✨";
                        setTimeout(() => btnTexto.innerText = "Salvar", 2500);
                    }
                    return true;
                } else {
                    alert("Erro ao salvar: " + (resultado.erro || "Falha desconhecida"));
                    if (!silencioso) btnTexto.innerText = "Salvar";
                    return false;
                }
            } catch (err) {
                console.error(err);
                if (!silencioso) {
                    btnTexto.innerText = "Salvar";
                    alert("Erro de conexão ao salvar.");
                }
                return false;
            }
        }

        // Finalizar e ir para Tela 3 (Finalizar)
        async function finalizarProjeto() {
            // Salva no banco de dados e no sessionStorage
            sessionStorage.setItem('projeto_finalizado_config', JSON.stringify(estado));
            await salvarProjetoNoBanco(true);
            window.location.href = `finalizar.php?id=${estado.id}`;
        }
    </script>
</body>
</html>
