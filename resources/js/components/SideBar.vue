<template>
  <b-sidebar
    :class="`sidebar ${sidebarCollapsed ? 'collapsed' : ''}`"
    width="inherit"
    :visible="true"
    header-class="sidebar-header"
    no-close-on-route-change
    @input-collapsed="onSidebarCollapsed"
  >
    <template #header>
      <template v-if="sidebarCollapsed">
        <b-button
          variant="link"
          size="md"
          class="float-right text-white px-3"
          @click="sidebarCollapsed = !sidebarCollapsed"
        >
          <i class="fas fa-ellipsis-v"></i>
        </b-button>
      </template>
      <template v-else>
        <img src="../../assets/logo.png" alt="Logo" style="height: 100%" />
        <div class="flex-grow-1 pl-1">{{ sidebarTitle }}</div>
        <b-button
          variant="link"
          size="md"
          class="float-right text-white"
          @click="sidebarCollapsed = !sidebarCollapsed"
        >
          <i :class="`fas fa-bars`" />
        </b-button>
      </template>
    </template>
    <template #default>
      <template v-for="item in sidebarItems">
        <Menu
          :item="item"
          :sidebarCollapsed="sidebarCollapsed"
          @handleMenu="handleMenu"
        />
      </template>
    </template>
  </b-sidebar>
</template>

<script>
import { mapActions } from "vuex";
import Menu from "./Menu.vue";

export default {
    data() {
        return {
            sidebarTitle: "App",
            showModal: false,
            sidebarCollapsed: false,
        };
    },
    computed: {
        currentPage() {
            return this.$route.path;
        },
        sidebarItems() {
            return this.$store.getters.getMenuItems;
        },
    },
    methods: {
        ...mapActions(["workflow/startProcess"]),
        isCurrentPage(item) {
            const currentItem = this.sidebarItems.find((item) => item.page === this.$route.path.substring(0, item.page.length));
            return currentItem === item;
        },
        handleMenu(item) {
            if (item.process) {
                this.startProcess({ processId: item.process, data: {} });
            }
            else if (item.page && item.page !== this.$route.path) {
                this.$router.push(item.page);
            }
        },
        toggle() {
            this.sidebarCollapsed = !this.sidebarCollapsed;
        },
        onSidebarCollapsed(collapsed) {
            this.sidebarCollapsed = collapsed;
        },
    },
    components: { Menu }
};
</script>

<style>
.sidebar {
  position: relative !important;
  width: 250px !important;
}
.sidebar .b-sidebar {
  background-color: white !important;
  box-shadow: 4px 4px 10px rgb(69 65 78 / 6%);
}
.sidebar.collapsed {
  width: 75px !important;
}
.sidebar.collapsed .b-sidebar-body {
  height: auto;
}
@media screen and (max-width: 991px) {
  .sidebar {
    width: 0px !important;
  }
  .sidebar.collapsed {
    width: 75px !important;
    height: 100vh;
    transform: translate3d(0px, 0, 0) !important;
  }
  .sidebar header > * {
    display: none !important;
  }
}
.sidebar-header {
  height: 62px;
  background-color: #1572e8 !important;
  justify-content: start;
  color: white;
  font-size: 1rem !important;
  font-weight: bold;
}
</style>
