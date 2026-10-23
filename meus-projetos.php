<?php
require_once __DIR__ . '/auth.php';
requer_login();

$usuario = obter_usuario();
$projetos = [];

try {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT id, titulo, criado_em, atualizado_em FROM projetos WHERE usuario_id = :uid ORDER BY atualizado_em DESC");
    $stmt->execute([':uid' => $usuario['id']]);
    $projetos = $stmt->fetchAll();
} catch (PDOException $e) {
    $erro = "Erro ao carregar projetos: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Projetos - PeriodicArt</title>
    <!-- Tailwind CSS -->
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
<body class="bg-[#f4f5fa] text-slate-800 min-h-screen flex flex-col font-sans">

    <!-- Header -->
    <header class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between sticky top-0 z-40 shadow-sm">
        <div class="flex items-center gap-4">
            <a href="index.php" class="flex items-center gap-2 group">
                <span class="text-3xl text-brand-600 transition group-hover:rotate-45">⚛️</span>
                <span class="text-2xl font-black text-slate-800 group-hover:text-brand-600 transition">PeriodicArt</span>
            </a>
            <span class="text-xs bg-slate-100 text-slate-600 font-bold px-3 py-1 rounded-full hidden sm:inline">
                Painel do Usuário
            </span>
        </div>

        <div class="flex items-center gap-3">
            <span class="text-xs text-slate-500 font-medium hidden sm:inline">
                Olá, <strong><?= htmlspecialchars(explode(' ', $usuario['nome'])[0]) ?></strong>
            </span>
            <a href="editor.php" class="bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-xs sm:text-sm px-4 py-2.5 rounded-xl shadow-md shadow-brand-600/25 transition">
                + Novo Projeto
            </a>
            <a href="logout.php" class="text-xs font-bold text-rose-600 hover:bg-rose-50 px-3 py-2 rounded-xl transition">
                Sair
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 max-w-5xl w-full mx-auto p-4 sm:p-8">
        
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-800">Minhas Tabelas Salvas</h1>
                <p class="text-sm text-slate-500 mt-1">Gerencie suas personalizações e continue editando a qualquer momento</p>
            </div>
            <a href="editor.php" class="inline-flex items-center gap-2 bg-brand-50 hover:bg-brand-100 text-brand-700 font-extrabold text-sm px-5 py-3 rounded-2xl border border-brand-200 transition">
                <span>✨</span>
                <span>Criar Nova Tabela</span>
            </a>
        </div>

        <?php if (!empty($erro)): ?>
            <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl text-sm">
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <?php if (empty($projetos)): ?>
            <!-- Empty State -->
            <div class="bg-white border border-slate-200/90 rounded-3xl p-12 text-center max-w-lg mx-auto shadow-sm my-8">
                <div class="w-16 h-16 bg-brand-50 text-brand-600 rounded-3xl flex items-center justify-center text-3xl mx-auto mb-4 shadow-inner">
                    🎨
                </div>
                <h3 class="text-lg font-black text-slate-800 mb-1">Nenhuma tabela salva ainda</h3>
                <p class="text-xs text-slate-500 mb-6 max-w-sm mx-auto">
                    Você ainda não salvou nenhuma tabela personalizada. Comece criando um modelo com suas cores e adesivos favoritos!
                </p>
                <a href="editor.php" class="inline-flex items-center gap-2 px-6 py-3.5 bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-sm rounded-xl shadow-lg shadow-brand-600/30 transition">
                    <span>COMEÇAR A PERSONALIZAR</span>
                    <span>→</span>
                </a>
            </div>
        <?php else: ?>
            <!-- Lista de Projetos -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($projetos as $proj): ?>
                    <div class="bg-white border border-slate-200/90 hover:border-brand-300 rounded-3xl p-6 shadow-sm hover:shadow-md transition flex flex-col justify-between group">
                        <div>
                            <!-- Header do Card -->
                            <div class="flex items-start justify-between gap-3 mb-4">
                                <div class="w-10 h-10 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center text-xl flex-shrink-0">
                                    ⚛️
                                </div>
                                <span class="text-[10px] bg-slate-100 text-slate-500 font-bold px-2 py-1 rounded-lg">
                                    <?= date('d/m/Y H:i', strtotime($proj['atualizado_em'])) ?>
                                </span>
                            </div>

                            <h3 class="text-base font-extrabold text-slate-800 group-hover:text-brand-600 transition truncate mb-1">
                                <?= htmlspecialchars($proj['titulo']) ?>
                            </h3>
                            <p class="text-xs text-slate-400">
                                Criado em: <?= date('d/m/Y', strtotime($proj['criado_em'])) ?>
                            </p>
                        </div>

                        <!-- Ações do Card -->
                        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between gap-2">
                            <a href="editor.php?id=<?= $proj['id'] ?>" 
                               class="flex-1 py-2 px-3 bg-brand-50 hover:bg-brand-100 text-brand-700 rounded-xl text-xs font-bold text-center transition flex items-center justify-center gap-1">
                                <span>✏️</span> Editar
                            </a>
                            <a href="finalizar.php?id=<?= $proj['id'] ?>" 
                               class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold text-center transition" title="Ver Concluído">
                                👁️
                            </a>
                            <button onclick="confirmarExclusao(<?= $proj['id'] ?>, '<?= htmlspecialchars(addslashes($proj['titulo'])) ?>')" 
                                    class="py-2 px-3 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl text-xs font-bold transition" title="Excluir">
                                🗑️
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-400 mt-12">
        © <?= date('Y') ?> PeriodicArt. Todos os direitos reservados.
    </footer>

    <script>
        function confirmarExclusao(id, titulo) {
            if (confirm(`Tem certeza de que deseja excluir o projeto "${titulo}"?`)) {
                window.location.href = `excluir_projeto.php?id=${id}`;
            }
        }
    </script>
</body>
</html>
