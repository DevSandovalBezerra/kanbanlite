<aside class="w-64 h-full bg-white border-r border-slate-200 hidden md:flex flex-col flex-shrink-0">
    <!-- Brand -->
    <div class="px-6 py-8 flex items-center gap-3">
        <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-100/50">
            <span class="material-symbols-outlined text-2xl font-bold">view_kanban</span>
        </div>
        <div class="flex flex-col">
            <span class="font-outfit text-xl font-bold tracking-tight text-slate-900">Kanban<span class="text-indigo-600">Lite</span></span>
            <span class="text-[10px] text-slate-500 font-medium uppercase tracking-widest leading-none mt-0.5">Gestão Profissional</span>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 px-4 space-y-1 overflow-y-auto mt-2">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest px-4 mb-2 mt-4">Principal</div>
        
        <a href="<?php echo $app_url ?? ''; ?>/" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all group">
            <span class="material-symbols-outlined text-[22px] group-hover:scale-110 transition-transform">dashboard</span>
            Painel Geral
        </a>

        <a href="<?php echo $app_url ?? ''; ?>/projects" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all group">
            <span class="material-symbols-outlined text-[22px] group-hover:scale-110 transition-transform">folder_open</span>
            Meus Projetos
        </a>
        
        <a href="<?php echo ($app_url ?? '') . '/boards' . ($first_board_id ?? 0 ? '?id=' . $first_board_id : ''); ?>" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 transition-all group">
            <span class="material-symbols-outlined text-[22px] group-hover:scale-110 transition-transform">view_kanban</span>
            Quadro Kanban
        </a>

        <a href="<?php echo $app_url ?? ''; ?>/calendar" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all group">
            <span class="material-symbols-outlined text-[22px] group-hover:scale-110 transition-transform">calendar_today</span>
            Calendário
        </a>

        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest px-4 mb-2 mt-8">Colaboração</div>

        <a href="<?php echo $app_url ?? ''; ?>/contacts" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all group">
            <span class="material-symbols-outlined text-[22px] group-hover:scale-110 transition-transform">group</span>
            Contatos
        </a>

        <a href="<?php echo $app_url ?? ''; ?>/messages" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all group">
            <span class="material-symbols-outlined text-[22px] group-hover:scale-110 transition-transform">chat_bubble_outline</span>
            Mensagens
        </a>
        
        <a href="<?php echo $app_url ?? ''; ?>/documents" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all group">
            <span class="material-symbols-outlined text-[22px] group-hover:scale-110 transition-transform">description</span>
            Documentos
        </a>

        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest px-4 mb-2 mt-8">Sistema</div>

        <?php if (!empty($user_is_admin)): ?>
        <a href="<?php echo $app_url ?? ''; ?>/admin/users" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-slate-600 hover:bg-rose-50 hover:text-rose-700 transition-all group">
            <span class="material-symbols-outlined text-[22px] group-hover:scale-110 transition-transform">manage_accounts</span>
            Gerenciar Usuários
        </a>
        <?php endif; ?>

        <a href="#" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all group opacity-60">
            <span class="material-symbols-outlined text-[22px] group-hover:scale-110 transition-transform">settings</span>
            Configurações
        </a>
    </nav>

    <!-- Sidebar Bottom: Profile Summary (Scribbs style) -->
    <div class="p-4 border-t border-slate-100 bg-slate-50/50">
        <div class="flex items-center gap-3 mt-1 px-2">

            <?php
                $initials = implode('', array_map(fn($w) => mb_strtoupper(mb_substr($w,0,1)),
                    array_slice(explode(' ', trim($user_name ?? 'U')), 0, 2)));
            ?>
            <div class="w-9 h-9 border-2 border-indigo-200 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                <?php echo htmlspecialchars($initials); ?>
            </div>
            <div class="flex flex-col flex-1 min-w-0">
                <span class="text-xs font-bold text-slate-900 truncate"><?php echo htmlspecialchars($user_name ?? ''); ?></span>
                <span class="text-[10px] text-slate-500 truncate"><?php echo htmlspecialchars($user_email ?? ''); ?></span>
            </div>
            <a href="<?php echo $app_url ?? ''; ?>/logout" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined text-lg">logout</span>
            </a>
        </div>
    </div>
</aside>

