<?php
require_once __DIR__ . '/auth.php';
$usuario = obter_usuario();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PeriodicArt - Personalize sua Tabela Periódica</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
                    }
                }
            }
        }
    </script>
    <style>
        .mini-grid {
            display: grid;
            grid-template-columns: repeat(18, minmax(0, 1fr));
            gap: 2px;
        }
    </style>
</head>
<body class="bg-[#f4f5fa] text-slate-800 min-h-screen flex flex-col font-sans selection:bg-brand-500 selection:text-white">

    <!-- Top Navigation Header -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-slate-200/80 px-4 sm:px-8 py-3.5 shadow-sm">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            
            <!-- Logo -->
            <a href="index.php" class="flex items-center gap-2.5 group">
                <span class="text-3xl text-brand-600 transition group-hover:rotate-180 duration-700 ease-in-out">⚛️</span>
                <span class="text-2xl font-black tracking-tight text-slate-800 group-hover:text-brand-600 transition">PeriodicArt</span>
            </a>

            <!-- Navigation Links -->
            <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-600">
                <a href="index.php" class="text-brand-600 font-bold border-b-2 border-brand-600 pb-0.5">Início</a>
                <a href="#como-funciona" class="hover:text-brand-600 transition">Sobre</a>
                <a href="#recursos" class="hover:text-brand-600 transition">Recursos</a>
                <?php if ($usuario): ?>
                    <a href="meus-projetos.php" class="hover:text-brand-600 transition flex items-center gap-1.5">
                        <span>📂</span> Meus Projetos
                    </a>
                <?php endif; ?>
                <a href="#contato" class="hover:text-brand-600 transition">Contato</a>
            </nav>

            <!-- User Auth / Action -->
            <div class="flex items-center gap-3">
                <?php if ($usuario): ?>
                    <div class="relative group">
                        <button class="flex items-center gap-2 bg-slate-100 hover:bg-slate-200 py-1.5 px-3.5 rounded-full text-sm font-bold text-slate-700 transition">
                            <span class="w-7 h-7 bg-brand-600 text-white rounded-full flex items-center justify-center text-xs font-black">
                                <?= strtoupper(substr($usuario['nome'], 0, 1)) ?>
                            </span>
                            <span class="hidden sm:inline"><?= htmlspecialchars(explode(' ', $usuario['nome'])[0]) ?></span>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-white border border-slate-200 rounded-2xl shadow-xl py-2 hidden group-hover:block group-focus-within:block z-50">
                            <a href="meus-projetos.php" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 font-medium">📂 Meus Projetos</a>
                            <a href="editor.php" class="block px-4 py-2 text-sm text-brand-600 hover:bg-brand-50 font-bold">✨ Novo Projeto</a>
                            <hr class="my-1.5 border-slate-100">
                            <a href="logout.php" class="block px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 font-medium">Sair da Conta</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="text-sm font-bold text-slate-700 hover:text-brand-600 px-3 py-2 transition">
                        Entrar
                    </a>
                    <a href="login.php?aba=cadastrar" class="hidden sm:inline-flex bg-brand-50 text-brand-700 hover:bg-brand-100 font-bold text-sm px-4 py-2 rounded-xl transition">
                        Cadastrar
                    </a>
                <?php endif; ?>
                <a href="editor.php" class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-extrabold px-4 sm:px-5 py-2.5 rounded-xl shadow-md shadow-brand-600/25 active:scale-95 transition">
                    Criar Agora
                </a>
            </div>
        </div>
    </header>

    <!-- Hero Section (Exatamente como o Card 1 do Mockup) -->
    <main class="flex-1 max-w-6xl w-full mx-auto px-4 sm:px-6 py-8 sm:py-12 flex flex-col items-center">
        
        <!-- Cartão Hero com Visual PeriodicArt -->
        <div class="w-full max-w-2xl bg-white border border-slate-200/90 rounded-3xl p-6 sm:p-10 shadow-lg shadow-brand-500/5 text-center relative overflow-hidden mb-12">
            
            <!-- Sparkles decorativos -->
            <div class="absolute top-6 left-8 text-2xl text-brand-400 select-none animate-pulse">✨</div>
            <div class="absolute top-10 right-8 text-2xl text-brand-400 select-none animate-pulse" style="animation-delay: 0.5s;">✨</div>
            <div class="absolute bottom-28 left-6 text-xl text-purple-300 select-none">✨</div>
            <div class="absolute bottom-32 right-6 text-xl text-purple-300 select-none">✨</div>

            <!-- Título Principal -->
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-slate-900 tracking-tight leading-tight mb-2">
                Personalize sua<br>
                <span class="text-brand-600">Tabela Periódica</span> ✨
            </h1>

            <p class="text-slate-600 text-base sm:text-lg font-medium mb-8">
                Deixe a química do seu jeito! ✨
            </p>

            <!-- Ilustração Vetorial da Tabela Periódica em Cores Pastel (Como no Mockup) -->
            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 sm:p-6 mb-8 max-w-lg mx-auto shadow-inner">
                <div class="grid grid-cols-18 gap-1 aspect-[18/10] text-[5px] sm:text-[7px] font-bold select-none">
                    <!-- Linha 1 -->
                    <div class="bg-[#fbcfe8] text-rose-800 rounded flex items-center justify-center p-0.5 border border-rose-300">H</div>
                    <div class="col-span-16"></div>
                    <div class="bg-[#e9d5ff] text-purple-800 rounded flex items-center justify-center p-0.5 border border-purple-300">He</div>

                    <!-- Linha 2 -->
                    <div class="bg-[#fed7aa] text-amber-800 rounded flex items-center justify-center p-0.5 border border-amber-300">Li</div>
                    <div class="bg-[#fef08a] text-yellow-800 rounded flex items-center justify-center p-0.5 border border-yellow-300">Be</div>
                    <div class="col-span-10"></div>
                    <div class="bg-[#bbf7d0] text-emerald-800 rounded flex items-center justify-center p-0.5 border border-emerald-300">B</div>
                    <div class="bg-[#fbcfe8] text-rose-800 rounded flex items-center justify-center p-0.5 border border-rose-300">C</div>
                    <div class="bg-[#fbcfe8] text-rose-800 rounded flex items-center justify-center p-0.5 border border-rose-300">N</div>
                    <div class="bg-[#fbcfe8] text-rose-800 rounded flex items-center justify-center p-0.5 border border-rose-300">O</div>
                    <div class="bg-[#cffafe] text-cyan-800 rounded flex items-center justify-center p-0.5 border border-cyan-300">F</div>
                    <div class="bg-[#e9d5ff] text-purple-800 rounded flex items-center justify-center p-0.5 border border-purple-300">Ne</div>

                    <!-- Linha 3 -->
                    <div class="bg-[#fed7aa] text-amber-800 rounded flex items-center justify-center p-0.5 border border-amber-300">Na</div>
                    <div class="bg-[#fef08a] text-yellow-800 rounded flex items-center justify-center p-0.5 border border-yellow-300">Mg</div>
                    <div class="col-span-10"></div>
                    <div class="bg-[#bae6fd] text-sky-800 rounded flex items-center justify-center p-0.5 border border-sky-300">Al</div>
                    <div class="bg-[#bbf7d0] text-emerald-800 rounded flex items-center justify-center p-0.5 border border-emerald-300">Si</div>
                    <div class="bg-[#fbcfe8] text-rose-800 rounded flex items-center justify-center p-0.5 border border-rose-300">P</div>
                    <div class="bg-[#fbcfe8] text-rose-800 rounded flex items-center justify-center p-0.5 border border-rose-300">S</div>
                    <div class="bg-[#cffafe] text-cyan-800 rounded flex items-center justify-center p-0.5 border border-cyan-300">Cl</div>
                    <div class="bg-[#e9d5ff] text-purple-800 rounded flex items-center justify-center p-0.5 border border-purple-300">Ar</div>

                    <!-- Linha 4 (Bloco de Transição) -->
                    <div class="bg-[#fed7aa] text-amber-800 rounded flex items-center justify-center p-0.5 border border-amber-300">K</div>
                    <div class="bg-[#fef08a] text-yellow-800 rounded flex items-center justify-center p-0.5 border border-yellow-300">Ca</div>
                    <div class="col-span-10 bg-[#bfdbfe] text-blue-800 rounded flex items-center justify-center tracking-widest text-[6px] border border-blue-300 font-extrabold">
                        METAIS DE TRANSIÇÃO (Sc ~ Zn)
                    </div>
                    <div class="bg-[#bae6fd] text-sky-800 rounded flex items-center justify-center p-0.5 border border-sky-300">Ga</div>
                    <div class="bg-[#bbf7d0] text-emerald-800 rounded flex items-center justify-center p-0.5 border border-emerald-300">Ge</div>
                    <div class="bg-[#bbf7d0] text-emerald-800 rounded flex items-center justify-center p-0.5 border border-emerald-300">As</div>
                    <div class="bg-[#fbcfe8] text-rose-800 rounded flex items-center justify-center p-0.5 border border-rose-300">Se</div>
                    <div class="bg-[#cffafe] text-cyan-800 rounded flex items-center justify-center p-0.5 border border-cyan-300">Br</div>
                    <div class="bg-[#e9d5ff] text-purple-800 rounded flex items-center justify-center p-0.5 border border-purple-300">Kr</div>

                    <!-- Espaço Lantanídeos/Actinídeos -->
                    <div class="col-span-18 h-1.5"></div>
                    <div class="col-span-2"></div>
                    <div class="col-span-14 bg-[#ddd6fe] text-indigo-800 rounded py-1 flex items-center justify-center text-[6px] border border-indigo-300 font-extrabold tracking-wider">
                        LANTANÍDEOS & ACTINÍDEOS (57-71 e 89-103)
                    </div>
                    <div class="col-span-2"></div>
                </div>
            </div>

            <!-- Botão de Ação Principal (COMEÇAR A PERSONALIZAR) -->
            <a href="editor.php" 
               class="inline-flex items-center justify-center gap-3 w-full sm:w-auto px-8 py-4 bg-brand-600 hover:bg-brand-700 active:scale-95 text-white font-extrabold text-base sm:text-lg rounded-2xl shadow-xl shadow-brand-600/30 transition-all duration-200">
                <span>COMEÇAR A PERSONALIZAR</span>
                <span class="text-xl">→</span>
            </a>
        </div>

        <!-- Seção: Como funciona? (Exatamente como o Card 1 do Mockup) -->
        <div id="como-funciona" class="w-full max-w-4xl mt-4">
            <h2 class="text-2xl font-black text-slate-800 text-center mb-8">Como funciona?</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- Passo 1 -->
                <div class="bg-white border border-slate-200/90 rounded-3xl p-6 text-center shadow-sm hover:shadow-md transition">
                    <div class="w-14 h-14 bg-brand-100 text-brand-700 rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
                        🖌️
                    </div>
                    <h3 class="font-extrabold text-base text-slate-800 mb-2">1. Personalize</h3>
                    <p class="text-sm text-slate-500 leading-relaxed">
                        Adicione cores, imagens, adesivos, logotipo da empresa e muito mais!
                    </p>
                </div>

                <!-- Passo 2 -->
                <div class="bg-white border border-slate-200/90 rounded-3xl p-6 text-center shadow-sm hover:shadow-md transition">
                    <div class="w-14 h-14 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
                        ✔️
                    </div>
                    <h3 class="font-extrabold text-base text-slate-800 mb-2">2. Finalize</h3>
                    <p class="text-sm text-slate-500 leading-relaxed">
                        Visualize em alta definição exatamente como ficou sua tabela periódica.
                    </p>
                </div>

                <!-- Passo 3 -->
                <div class="bg-white border border-slate-200/90 rounded-3xl p-6 text-center shadow-sm hover:shadow-md transition">
                    <div class="w-14 h-14 bg-sky-100 text-sky-700 rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
                        📥
                    </div>
                    <h3 class="font-extrabold text-base text-slate-800 mb-2">3. Baixe ou imprima</h3>
                    <p class="text-sm text-slate-500 leading-relaxed">
                        Baixe em PNG, JPEG, SVG vetor puro, PDF ou imprima do jeito que quiser!
                    </p>
                </div>

            </div>
        </div>

        <!-- Recursos Científicos e Regras de Negócio -->
        <section id="recursos" class="w-full max-w-4xl mt-16 bg-white border border-slate-200/80 rounded-3xl p-8 sm:p-10 shadow-sm">
            <div class="flex items-center gap-3 mb-6">
                <span class="text-2xl">🔬</span>
                <h2 class="text-xl font-black text-slate-800">100% Preciso e Oficial</h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-slate-600">
                <div class="flex items-start gap-3">
                    <span class="text-emerald-500 font-bold text-lg">✓</span>
                    <span><strong>Todos os 118 Elementos Reais:</strong> Do Hidrogênio ao Oganessônio, sem elementos inventados.</span>
                </div>
                <div class="flex items-start gap-3">
                    <span class="text-emerald-500 font-bold text-lg">✓</span>
                    <span><strong>Massa com 5 Algarismos:</strong> Rigorosamente alinhado à tabela oficial da IUPAC.</span>
                </div>
                <div class="flex items-start gap-3">
                    <span class="text-emerald-500 font-bold text-lg">✓</span>
                    <span><strong>Modelo Vetorizado SVG:</strong> Cabe perfeitamente em qualquer resolução e tela sem perda de qualidade.</span>
                </div>
                <div class="flex items-start gap-3">
                    <span class="text-emerald-500 font-bold text-lg">✓</span>
                    <span><strong>Salvar e Continuar:</strong> Seus designs ficam salvos com segurança no MySQL com sua conta.</span>
                </div>
            </div>
        </section>

    </main>

    <!-- Footer (Exatamente como no Mockup) -->
    <footer id="contato" class="bg-white border-t border-slate-200 mt-16 py-8 px-4 sm:px-8">
        <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-slate-500">
            <div class="flex items-center gap-2">
                <span class="text-xl">⚛️</span>
                <span>© <?= date('Y') ?> PeriodicArt. Todos os direitos reservados.</span>
            </div>

            <div class="flex items-center gap-6">
                <a href="#" class="hover:text-brand-600 transition">Termos</a>
                <a href="#" class="hover:text-brand-600 transition">Privacidade</a>
            </div>

            <!-- Redes Sociais -->
            <div class="flex items-center gap-4 text-lg text-slate-600">
                <a href="#" title="Instagram" class="hover:text-pink-600 transition">📸</a>
                <a href="#" title="WhatsApp" class="hover:text-emerald-600 transition">💬</a>
                <a href="#" title="TikTok" class="hover:text-slate-900 transition">🎵</a>
            </div>
        </div>
    </footer>

</body>
</html>
