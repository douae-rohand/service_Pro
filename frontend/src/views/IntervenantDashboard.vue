<template>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <aside :class="['sidebar', { 'sidebar-open': isSidebarOpen }]">
      <!-- Sidebar Header -->
      <div class="sidebar-header">
        <div class="sidebar-header-content">
          <h2 class="brand">ServicePro</h2>
          <button @click="isSidebarOpen = false" class="close-btn">
            <X :size="20" />
          </button>
        </div>
        <p class="sidebar-subtitle">Espace Intervenant</p>
      </div>

      <!-- Navigation -->
      <nav class="nav">
        <ul class="nav-list">
          <li v-for="tab in tabs" :key="tab.id">
            <router-link
              :to="`/dashboard/${tab.id}`"
              class="nav-link"
              :class="{ 'nav-link-active': isActiveTab(tab.id) }"
              :style="isActiveTab(tab.id) ? { backgroundColor: tab.color, color: 'white' } : {}"
              @click="isSidebarOpen = false"
            >
              <component :is="tab.icon" :size="20" />
              <span>{{ tab.label }}</span>
            </router-link>
          </li>
        </ul>
      </nav>
    </aside>

    <!-- Overlay for mobile -->
    <div v-if="isSidebarOpen" class="overlay" @click="isSidebarOpen = false"></div>

    <!-- Main Content -->
    <div class="main-content">
      <!-- Top Navbar -->
      <header class="header">
        <div class="header-container">
          <div class="header-left">
            <!-- Mobile Menu Button -->
            <button @click="isSidebarOpen = true" class="menu-btn">
              <Menu :size="24" />
            </button>

            <!-- Page Title -->
            <h1 class="page-title">{{ currentPageTitle }}</h1>
          </div>

          <!-- Profile Dropdown -->
          <div class="profile-section">
            <button @click="showProfileMenu = !showProfileMenu" class="profile-btn">
              <img :src="intervenant.profileImage" :alt="intervenant.name" class="profile-image" />
              <div class="profile-info">
                <p class="profile-name">{{ intervenant.name }}</p>
                <p class="profile-location">{{ intervenant.location }}</p>
              </div>
            </button>

            <!-- Dropdown Menu -->
            <div v-if="showProfileMenu" class="profile-dropdown-wrapper">
              <div class="dropdown-overlay" @click="showProfileMenu = false"></div>
              <div class="profile-dropdown">
                <div class="dropdown-header">
                  <p class="dropdown-name">{{ intervenant.name }}</p>
                  <p class="dropdown-email">{{ intervenant.email }}</p>
                </div>
                <router-link to="/dashboard/profile" class="dropdown-item" @click="showProfileMenu = false">
                  <User :size="18" />
                  <span>Mon Profil</span>
                </router-link>
                <div class="dropdown-divider"></div>
                <button @click="handleLogout" class="dropdown-item logout-btn">
                  <LogOut :size="18" />
                  <span>Déconnexion</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Tab Content -->
      <main class="content">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Menu, X, User, LogOut, Briefcase, Calendar, Star, ClipboardList, History, MessageCircle } from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()

const isSidebarOpen = ref(false)
const showProfileMenu = ref(false)

// Mock intervenant data
const intervenant = ref({
  id: 1,
  name: 'Amina Chakir',
  email: 'amina.chakir@email.com',
  phone: '+212 6 12 34 56 78',
  profileImage: 'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?w=150&h=150&fit=crop',
  location: 'Tetouan Centre',
  memberSince: '2019'
})

const tabs = [
  { id: 'services', label: 'Mes Services', icon: Briefcase, color: '#92B08B' },
  { id: 'myservices', label: 'Sous-services Actifs', icon: ClipboardList, color: '#1A5FA3' },
  { id: 'reservations', label: 'Réservations', icon: Calendar, color: '#E8793F' },
  { id: 'availability', label: 'Disponibilités', icon: Calendar, color: '#A5D4E6'},
  { id: 'reviewclients', label: 'Évaluer Clients', icon: MessageCircle, color: '#FEE347' },
  { id: 'reviewsstats', label: 'Mes Avis & Notes', icon: Star, color: '#E8793F' },
  { id: 'history', label: 'Historique', icon: History, color: '#92B08B' }
]

const isActiveTab = (tabId) => {
  return route.name === tabId
}

const currentPageTitle = computed(() => {
  const currentTab = tabs.find(t => t.id === route.name)
  return currentTab?.label || 'Tableau de bord'
})

const handleLogout = () => {
  // In real app, clear auth and redirect to login
  alert('Déconnexion...')
  showProfileMenu.value = false
}
</script>

<style scoped>
.dashboard-container {
  display: flex;
  min-height: 100vh;
  background-color: var(--color-gray-50);
}

/* Sidebar Styles */
.sidebar {
  position: fixed;
  left: 0;
  top: 0;
  bottom: 0;
  width: 16rem;
  background: var(--color-white);
  box-shadow: var(--shadow-lg);
  transform: translateX(-100%);
  transition: transform 0.3s ease;
  z-index: 50;
}

.sidebar-open {
  transform: translateX(0);
}

.sidebar-header {
  padding: var(--spacing-6);
  border-bottom: 1px solid var(--color-gray-200);
}

.sidebar-header-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.brand {
  font-size: 1.5rem;
  color: var(--color-primary-green);
  margin: 0;
}

.close-btn {
  display: block;
  padding: var(--spacing-2);
  border-radius: var(--radius-lg);
  transition: background-color 0.2s;
}

.close-btn:hover {
  background-color: var(--color-gray-100);
}

.sidebar-subtitle {
  font-size: 0.875rem;
  color: var(--color-gray-600);
  margin-top: var(--spacing-1);
}

.nav {
  padding: var(--spacing-4);
}

.nav-list {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: var(--spacing-2);
}

.nav-link {
  display: flex;
  align-items: center;
  gap: var(--spacing-3);
  padding: var(--spacing-3) var(--spacing-4);
  border-radius: var(--radius-lg);
  color: var(--color-dark);
  transition: all 0.2s;
  text-decoration: none;
}

.nav-link:hover {
  background-color: var(--color-gray-50);
}

.nav-link-active {
  box-shadow: var(--shadow-md);
}

/* Overlay */
.overlay {
  position: fixed;
  inset: 0;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 40;
}

/* Main Content */
.main-content {
  display: flex;
  flex-direction: column;
  flex: 1;
  min-height: 100vh;
}

.header {
  background: var(--color-white);
  box-shadow: var(--shadow-sm);
  position: sticky;
  top: 0;
  z-index: 30;
}

.header-container {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: var(--spacing-4) var(--spacing-6);
}

.header-left {
  display: flex;
  align-items: center;
  gap: var(--spacing-4);
  flex: 1;
}

.menu-btn {
  display: flex;
  padding: var(--spacing-2);
  border-radius: var(--radius-lg);
  transition: background-color 0.2s;
}

.menu-btn:hover {
  background-color: var(--color-gray-100);
}

.page-title {
  font-size: 1.5rem;
  color: var(--color-dark);
  margin: 0;
}

.profile-section {
  position: relative;
}

.profile-btn {
  display: flex;
  align-items: center;
  gap: var(--spacing-3);
  padding: var(--spacing-2) var(--spacing-4);
  border-radius: var(--radius-lg);
  transition: background-color 0.2s;
}

.profile-btn:hover {
  background-color: var(--color-gray-50);
}

.profile-image {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid var(--color-primary-green);
}

.profile-info {
  display: none;
  text-align: left;
}

.profile-name {
  font-size: 0.875rem;
  color: var(--color-dark);
  margin: 0;
}

.profile-location {
  font-size: 0.75rem;
  color: var(--color-gray-500);
  margin: 0;
}

.profile-dropdown-wrapper {
  position: relative;
}

.dropdown-overlay {
  position: fixed;
  inset: 0;
  z-index: 10;
}

.profile-dropdown {
  position: absolute;
  right: 0;
  top: 0.5rem;
  width: 16rem;
  background: var(--color-white);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-xl);
  border: 1px solid var(--color-gray-200);
  padding: var(--spacing-2) 0;
  z-index: 20;
}

.dropdown-header {
  padding: var(--spacing-3) var(--spacing-4);
  border-bottom: 1px solid var(--color-gray-200);
}

.dropdown-name {
  font-size: 0.875rem;
  color: var(--color-dark);
  margin: 0;
}

.dropdown-email {
  font-size: 0.75rem;
  color: var(--color-gray-500);
  margin: 0;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: var(--spacing-3);
  padding: var(--spacing-3) var(--spacing-4);
  width: 100%;
  text-align: left;
  color: var(--color-dark);
  transition: background-color 0.2s;
  text-decoration: none;
}

.dropdown-item:hover {
  background-color: var(--color-gray-50);
}

.logout-btn {
  color: var(--color-orange);
}

.logout-btn:hover {
  background-color: #FEF3E6;
}

.dropdown-divider {
  height: 1px;
  background-color: var(--color-gray-200);
  margin: var(--spacing-2) 0;
}

.content {
  flex: 1;
  padding: var(--spacing-8) var(--spacing-6);
}

/* Desktop */
@media (min-width: 1024px) {
  .sidebar {
    position: static;
    transform: translateX(0);
  }

  .close-btn {
    display: none;
  }

  .menu-btn {
    display: none;
  }

  .profile-info {
    display: block;
  }
}

/* Tablet */
@media (max-width: 768px) {
  .content {
    padding: var(--spacing-4);
  }
  
  .header-container {
    padding: var(--spacing-4);
  }
}
</style>
