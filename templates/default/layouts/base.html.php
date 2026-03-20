<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $csrf_token ?? ''; ?>">
    <title><?php echo $title ?? 'KanbanLite - Gestão Ágil'; ?></title>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#6366F1',
                            light: '#EEF2FF',
                            dark: '#4F46E5'
                        },
                        surface: '#F8FAFC',
                        'sidebar-bg': '#FFFFFF',
                        'sidebar-active': '#F1F5F9'
                    }
                }
            }
        }
    </script>
    <!-- Google Fonts: Inter & Outfit -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Material Symbols -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #F8FAFC; color: #1E293B; }
        .font-outfit { font-family: 'Outfit', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24 }
        .sidebar-item-active { background-color: #F1F5F9; color: #6366F1; font-weight: 600; border-right: 4px solid #6366F1; }
        .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); }
    </style>
    <?php if (isset($extra_css)) echo $extra_css; ?>
</head>
<body class="h-screen flex overflow-hidden">
    <!-- Sidebar -->
    <?php require self::resolvePath('partials.sidebar'); ?>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <!-- Navbar -->
        <?php require self::resolvePath('partials.navbar'); ?>
        
        <!-- Page Content -->
        <main class="flex-1 overflow-auto bg-surface">
            <?php echo $content; ?>
        </main>

        <!-- Footer (opcional na main) -->
        <?php // require self::resolvePath('partials.footer'); ?>
    </div>
    
    <?php if (isset($extra_js)) echo $extra_js; ?>
</body>
</html>
