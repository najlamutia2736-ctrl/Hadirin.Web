 <aside class="w-64 bg-white border-r border-gray-200 flex flex-col shadow-sm">
     <!-- brand / logo -->
     <div class="h-16 flex items-center px-6 border-b border-gray-200">
         <i class="fas fa-school text-indigo-600 text-2xl mr-3"></i>
         <span class="text-xl font-semibold text-gray-800 tracking-tight">EduPanel</span>
     </div>

     <!-- menu navigasi -->
     <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
         <!-- Dashboard (aktif) -->
         <a href="/dashboard"
             class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('cms.dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700' }}">
             <i
                 class="fas fa-tachometer-alt w-5 {{ request()->routeIs('cms.dashboard') ? 'text-indigo-600' : 'text-gray-500' }}"></i>
             <span class="ml-3">Dashboard</span>
         </a>

         <!-- Students -->
         <a href="/students"
             class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('cms.students') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700' }}">
             <i
                 class="fas fa-user-graduate w-5 {{ request()->routeIs('cms.students') ? 'text-indigo-600' : 'text-gray-500' }}"></i>
             <span class="ml-3">Students</span>
         </a>

         <!-- Teachers -->
         <a href="/teachers"
             class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('cms.teachers') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700' }}">
             <i
                 class="fas fa-chalkboard-teacher w-5 {{ request()->routeIs('cms.teachers') ? 'text-indigo-600' : 'text-gray-500' }}"></i>
             <span class="ml-3">Teachers</span>
         </a>

         <!-- Classes -->
         <a href="/classes"
             class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('cms.classes') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700' }}">
             <i
                 class="fas fa-book-open w-5 {{ request()->routeIs('cms.classes') ? 'text-indigo-600' : 'text-gray-500' }}"></i>
             <span class="ml-3">Classes</span>
         </a>

         <!-- Users -->
         <a href="/users"
             class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('cms.users') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700' }}">
             <i
                 class="fas fa-users-cog w-5 {{ request()->routeIs('cms.users') ? 'text-indigo-600' : 'text-gray-500' }}"></i>
             <span class="ml-3">Users</span>
         </a>

         <!-- Rekap Absen -->
         <a href="/rekap"
             class="sidebar-link flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('cms.rekap') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700' }}">
             <i
                 class="fas fa-users-cog w-5 {{ request()->routeIs('cms.rekap') ? 'text-indigo-600' : 'text-gray-500' }}"></i>
             <span class="ml-3">Rekap</span>
         </a>
     </nav>

     <!-- footer sidebar (user info) -->
     <div class="p-4 border-t border-gray-200">
         <div class="flex items-center">
             <div
                 class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-semibold text-sm">
                 AD
             </div>
             <div class="ml-3">
                 <p class="text-sm font-medium text-gray-700">Admin</p>
                 <p class="text-xs text-gray-500">admin@sekolah.id</p>
             </div>
         </div>
     </div>
 </aside>
