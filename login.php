<?php
require_once __DIR__ . '/auth.php';

$erro = '';
$sucesso = '';
$abaAtiva = $_GET['aba'] ?? 'login';
$redirect = $_GET['redirect'] ?? 'editor.php';

// Se já estiver logado, redireciona direto
if (usuario_logado()) {
    header("Location: {$redirect}");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? 'login';

    if ($acao === 'login') {
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';
        $res = fazer_login($email, $senha);
        if ($res['sucesso']) {
            header("Location: {$redirect}");
            exit;
        } else {
            $erro = $res['erro'];
            $abaAtiva = 'login';
        }
    } elseif ($acao === 'cadastrar') {
        $nome  = $_POST['nome'] ?? '';
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';
        $res = cadastrar_usuario($nome, $email, $senha);
        if ($res['sucesso']) {
            header("Location: {$redirect}");
            exit;
        } else {
            $erro = $res['erro'];
            $abaAtiva = 'cadastrar';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar ou Cadastrar - PeriodicArt</title>
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
</head>
<body class="bg-[#f5f6fa] text-slate-800 min-h-screen flex flex-col justify-between font-sans">

    <!-- Header Simples -->
    <header class="w-full bg-white border-b border-slate-200 py-4 px-6 shadow-sm">
        <div class="max-w-5xl mx-auto flex items-center justify-between">
            <a href="index.php" class="flex items-center gap-3 group">
                <span class="text-3xl text-brand-600 transition group-hover:rotate-12 transform">⚛️</span>
                <span class="text-2xl font-extrabold tracking-tight text-slate-800 group-hover:text-brand-600 transition">PeriodicArt</span>
            </a>
            <a href="index.php" class="text-sm font-semibold text-slate-600 hover:text-brand-600 flex items-center gap-1 transition">
                ← Voltar ao Início
            </a>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 flex items-center justify-center p-4 sm:p-6 my-8">
        <div class="w-full max-w-md bg-white rounded-3xl shadow-xl shadow-brand-500/5 border border-slate-200/80 p-8 sm:p-10 relative overflow-hidden">
            
            <!-- Detalhe decorativo no topo -->
            <div class="absolute -top-12 -right-12 w-32 h-32 bg-brand-100 rounded-full blur-2xl pointer-events-none"></div>

            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-14 h-14 bg-brand-50 rounded-2xl text-brand-600 text-3xl mb-3 shadow-inner">
                    ⚛️
                </div>
                <h1 class="text-2xl font-extrabold text-slate-800">Bem-vindo ao PeriodicArt</h1>
                <p class="text-sm text-slate-500 mt-1">Acesse para salvar e personalizar suas tabelas periódicas</p>
            </div>

            <!-- Abas Login / Cadastro -->
            <div class="flex bg-slate-100 p-1 rounded-2xl mb-6">
                <button type="button" onclick="mudarAba('login')" id="tab-login"
                        class="flex-1 py-2.5 text-sm font-bold rounded-xl transition-all duration-200 <?= $abaAtiva === 'login' ? 'bg-white text-brand-600 shadow-sm' : 'text-slate-500 hover:text-slate-800' ?>">
                    Entrar
                </button>
                <button type="button" onclick="mudarAba('cadastrar')" id="tab-cadastrar"
                        class="flex-1 py-2.5 text-sm font-bold rounded-xl transition-all duration-200 <?= $abaAtiva === 'cadastrar' ? 'bg-white text-brand-600 shadow-sm' : 'text-slate-500 hover:text-slate-800' ?>">
                    Criar Conta
                </button>
            </div>

            <!-- Mensagens de Alerta -->
            <?php if (!empty($erro)): ?>
                <div class="mb-5 p-3.5 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-sm flex items-center gap-2">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <span><?= htmlspecialchars($erro) ?></span>
                </div>
            <?php endif; ?>

            <!-- Formulário de Login -->
            <form id="form-login" method="POST" action="login.php?redirect=<?= urlencode($redirect) ?>" class="<?= $abaAtiva === 'login' ? 'block' : 'hidden' ?> space-y-4">
                <input type="hidden" name="acao" value="login">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5" for="login-email">E-mail</label>
                    <input type="email" id="login-email" name="email" required placeholder="seu@email.com"
                           class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition text-sm">
                </div>
                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600" for="login-senha">Senha</label>
                    </div>
                    <input type="password" id="login-senha" name="senha" required placeholder="••••••••"
                           class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition text-sm">
                </div>

                <button type="submit"
                        class="w-full py-3.5 px-4 bg-brand-600 hover:bg-brand-700 active:scale-[0.99] text-white font-bold rounded-xl shadow-lg shadow-brand-600/30 transition-all text-sm mt-2">
                    Entrar e Personalizar →
                </button>
            </form>

            <!-- Formulário de Cadastro -->
            <form id="form-cadastrar" method="POST" action="login.php?redirect=<?= urlencode($redirect) ?>" class="<?= $abaAtiva === 'cadastrar' ? 'block' : 'hidden' ?> space-y-4">
                <input type="hidden" name="acao" value="cadastrar">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5" for="cad-nome">Nome Completo</label>
                    <input type="text" id="cad-nome" name="nome" required placeholder="Maria Curie"
                           class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5" for="cad-email">E-mail</label>
                    <input type="email" id="cad-email" name="email" required placeholder="maria@exemplo.com"
                           class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5" for="cad-senha">Senha</label>
                    <input type="password" id="cad-senha" name="senha" required placeholder="Mínimo 4 caracteres"
                           class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition text-sm">
                </div>

                <button type="submit"
                        class="w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] text-white font-bold rounded-xl shadow-lg shadow-emerald-600/30 transition-all text-sm mt-2">
                    Criar Minha Conta Grátis ✨
                </button>
            </form>

            <!-- Dica rápida de teste -->
            <div class="mt-6 pt-5 border-t border-slate-100 text-center">
                <button type="button" onclick="preencherDemo()" class="text-xs font-semibold text-brand-600 hover:text-brand-800 transition">
                    🧪 Preencher com conta de teste rápida
                </button>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-5 text-center text-xs text-slate-400">
        © <?= date('Y') ?> PeriodicArt. Todos os direitos reservados.
    </footer>

    <script>
        function mudarAba(aba) {
            const formLogin = document.getElementById('form-login');
            const formCadastrar = document.getElementById('form-cadastrar');
            const tabLogin = document.getElementById('tab-login');
            const tabCadastrar = document.getElementById('tab-cadastrar');

            if (aba === 'login') {
                formLogin.classList.remove('hidden');
                formCadastrar.classList.add('hidden');
                tabLogin.className = 'flex-1 py-2.5 text-sm font-bold rounded-xl transition-all duration-200 bg-white text-brand-600 shadow-sm';
                tabCadastrar.className = 'flex-1 py-2.5 text-sm font-bold rounded-xl transition-all duration-200 text-slate-500 hover:text-slate-800';
            } else {
                formCadastrar.classList.remove('hidden');
                formLogin.classList.add('hidden');
                tabCadastrar.className = 'flex-1 py-2.5 text-sm font-bold rounded-xl transition-all duration-200 bg-white text-brand-600 shadow-sm';
                tabLogin.className = 'flex-1 py-2.5 text-sm font-bold rounded-xl transition-all duration-200 text-slate-500 hover:text-slate-800';
            }
        }

        function preencherDemo() {
            mudarAba('cadastrar');
            document.getElementById('cad-nome').value = 'Estudante de Química';
            document.getElementById('cad-email').value = 'quimica@exemplo.com';
            document.getElementById('cad-senha').value = '123456';
        }
    </script>
</body>
</html>
