<header class="h-16 flex items-center justify-between px-6 bg-white border-b border-slate-200 sticky top-0 z-20 shadow-sm glass">
    <div class="flex items-center flex-1 max-w-xl relative">
        <div class="relative w-full">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">search</span>
            <input type="text" id="global-search" placeholder="Pesquise projetos, quadros ou pessoas..." class="w-full h-10 pl-11 pr-4 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 placeholder:text-slate-400 transition-all">
            <kbd class="absolute right-3 top-1/2 -translate-y-1/2 px-1.5 py-0.5 bg-white border border-slate-200 rounded text-[10px] text-slate-400 font-bold hidden sm:block">⌘K</kbd>
        </div>

        <!-- Search Results Dropdown -->
        <div id="search-results" class="hidden absolute top-12 left-0 w-full bg-white border border-slate-100 rounded-2xl shadow-2xl z-50 overflow-hidden animate-in fade-in slide-in-from-top-2 duration-200">
            <div class="p-2 space-y-1" id="search-results-list">
                <!-- Results injected here -->
            </div>
            <div class="p-3 bg-slate-50 border-t border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">
                Pressione Enter para ver tudo
            </div>
        </div>
    </div>
    
    <div class="flex items-center gap-2 sm:gap-4 pl-4">
        <div class="hidden sm:flex items-center gap-1 bg-indigo-50/50 px-3 py-1.5 rounded-full border border-indigo-100">
            <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
            <span class="text-[10px] font-bold text-indigo-700 uppercase tracking-widest whitespace-nowrap">Online em tempo real</span>
        </div>

        <a href="<?php echo $app_url ?? ''; ?>/messages" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-slate-50 rounded-xl transition-all relative">
            <span class="material-symbols-outlined text-[24px]">chat_bubble_outline</span>
            <span class="absolute right-2 top-2 block h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
        </a>

        <button class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-slate-50 rounded-xl transition-all relative">
            <span class="material-symbols-outlined text-[24px]">notifications</span>
        </button>

        <div class="h-8 w-px bg-slate-200 mx-1"></div>

        <div class="flex items-center gap-3 cursor-pointer group">
            <div class="flex flex-col text-right hidden sm:flex truncate max-w-[150px]">
                <span class="text-xs font-bold text-slate-900 leading-none">Administrador</span>
                <span class="text-[10px] text-slate-500 font-medium leading-tight mt-0.5">Gestor de Projeto</span>
            </div>
            <div class="w-10 h-10 border-2 border-slate-100 rounded-xl bg-indigo-100/50 flex items-center justify-center text-indigo-700 font-bold text-sm shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Felix" alt="Avatar">
            </div>
        </div>
    </div>
</header>

<script>
    const searchInput = document.getElementById('global-search');
    const resultsPanel = document.getElementById('search-results');
    const resultsList = document.getElementById('search-results-list');

    searchInput.addEventListener('input', async (e) => {
        const q = e.target.value;
        if (q.length < 2) {
            resultsPanel.classList.add('hidden');
            return;
        }

        try {
            const url = '<?php echo $app_url; ?>/api/search?q=' + encodeURIComponent(q);
            const response = await fetch(url);
            const data = await response.json();

            if (data.results && data.results.length > 0) {
                resultsList.innerHTML = '';
                data.results.forEach(res => {
                    const item = document.createElement('a');
                    item.href = '<?php echo $app_url; ?>/boards?id=' + res.id;
                    item.className = 'flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 transition-colors group';

                    item.innerHTML = `
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <span class="material-symbols-outlined text-sm font-bold">${res.type === 'board' ? 'view_kanban' : 'task'}</span>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-bold text-slate-900">${res.name}</p>
                            <p class="text-[10px] text-slate-400 capitalize">${res.type === 'board' ? 'Quadro' : 'Tarefa'}</p>
                        </div>
                    `;
                    resultsList.appendChild(item);
                });
                resultsPanel.classList.remove('hidden');
            } else {
                resultsPanel.classList.add('hidden');
            }
        } catch (err) {
            console.error(err);
        }
    });

    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !resultsPanel.contains(e.target)) {
            resultsPanel.classList.add('hidden');
        }
    });
</script>
