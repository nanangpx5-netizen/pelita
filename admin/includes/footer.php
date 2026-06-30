<?php
/**
 * Admin Landing Page
 * Theme: Glassmorphism Dashboard
 */

// Footer is in includes/footer.php
?>
            </div>
        </main>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 hidden lg:hidden transition-opacity duration-300" onclick="toggleSidebar()"></div>

    <!-- Mobile Sidebar Drawer -->
    <aside id="mobileSidebar" class="fixed inset-y-0 left-0 w-72 glass-sidebar z-50 transform -translate-x-full transition-transform duration-300 lg:hidden flex flex-col h-full bg-white/90">
        <!-- Brand -->
        <div class="h-20 flex items-center justify-between px-8 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-bps-blue flex items-center justify-center text-white shadow-md">
                    <i class="fa-solid fa-chart-simple"></i>
                </div>
                <h1 class="text-xl font-bold text-bps-blue">PELITA</h1>
            </div>
            <button onclick="toggleSidebar()" class="text-slate-400 hover:text-slate-600 transition">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        <!-- Copied Navigation Logic via JS or duplicated structure -->
        <nav class="flex-1 overflow-y-auto p-4 space-y-2">
            <a href="<?= base_url('admin/') ?>" class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-slate-600 hover:bg-bps-blue/10 hover:text-bps-blue transition-all">
                <i class="fa-solid fa-chart-pie w-5"></i> Dashboard
            </a>
            <a href="<?= base_url('admin/buku-tamu/') ?>" class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-slate-600 hover:bg-bps-blue/10 hover:text-bps-blue transition-all">
                <i class="fa-solid fa-book-open w-5"></i> Buku Tamu
            </a>
            <a href="<?= base_url('admin/kepuasan/') ?>" class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-slate-600 hover:bg-bps-blue/10 hover:text-bps-blue transition-all">
                <i class="fa-solid fa-star w-5"></i> Survei Kepuasan
            </a>
            <a href="<?= base_url('admin/antrian/') ?>" class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-slate-600 hover:bg-bps-blue/10 hover:text-bps-blue transition-all">
                <i class="fa-solid fa-ticket w-5"></i> Antrian
            </a>
            <div class="my-4 border-t border-gray-100"></div>
            <a href="https://halopst.web.bps.go.id/?mfd=3500" target="_blank" class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-slate-600 hover:bg-bps-blue/10 hover:text-bps-blue transition-all">
                <i class="fa-solid fa-headset w-5"></i> Halo PST <i class="fa-solid fa-up-right-from-square text-[10px] ml-auto opacity-50"></i>
            </a>
            <a href="https://skd.bps.go.id/skd/p/3509" target="_blank" class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-slate-600 hover:bg-bps-blue/10 hover:text-bps-blue transition-all">
                <i class="fa-solid fa-clipboard-question w-5"></i> Survei Kebutuhan Data <i class="fa-solid fa-up-right-from-square text-[10px] ml-auto opacity-50"></i>
            </a>
            <a href="<?= base_url('admin/logout.php') ?>" class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-red-500 hover:bg-red-50 transition-all mt-4">
                <i class="fa-solid fa-right-from-bracket w-5"></i> Keluar
            </a>
        </nav>
    </aside>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('mobileSidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            if (sidebar.classList.contains('-translate-x-full')) {
                // Open
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                setTimeout(() => overlay.classList.remove('opacity-0'), 10);
            } else {
                // Close
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('opacity-0');
                setTimeout(() => overlay.classList.add('hidden'), 300);
            }
        }
    </script>
</body>
</html>
