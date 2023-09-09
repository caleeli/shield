<template>
  <div class="layout">
    <SideBar ref="sidebar" class="sidebar" />
    <NavBar class="navbar" @toggle-sidebar="$refs.sidebar.toggle()" />
    <div class="body">
        <b-breadcrumb v-if="items.length">
          <b-breadcrumb-item
            v-for="(item, index) in items"
            :key="index"
            :active="index === items.length - 1"
            :to="item.page"
          >
            <i :class="`fas fa-${item.icon}`" class="mr-2"></i>
            {{ item.text }}
          </b-breadcrumb-item>
          <b v-if="nota" class="flex-grow-1 text-right">
            {{ nota }}
          </b>
        </b-breadcrumb>
      <b-card class="flex-grow-1 overflow-auto" :class="{'disable-controls': readOnly}">
        <router-view />
      </b-card>
    </div>
  </div>
</template>

<script>
import SideBar from "./SideBar.vue";
import NavBar from "./NavBar.vue";
import { mapState } from "vuex";

export default {
  components: {
    SideBar,
    NavBar,
  },
  data() {
    return {
      nota: "",
    };
  },
  computed: {
    ...mapState({
      workflowPage: state => state.workflow.page,
      workflowTokens: state => state.workflow.tokens,
    }),
    readOnly() {
      return this.$route.query.record ? true : false;
    },
    menuItems() {
      return this.$store.getters.getMenuItems;
    },
    items() {
      const items = [];
      const currentPath = this.$route.path;
      if (!currentPath) {
        return items;
      }
      const token = this.workflowTokens && this.workflowTokens.find(t => currentPath.endsWith(t.implementation));
      if (token) {
        items.push({
          text: token.name,
          page: token.implementation,
          icon: '',
        });
        return items;
      }
      const currentMenu = this.flatMenuItems(this.menuItems)
        .sort((a, b) => b.page?.length - a.page?.length)
        // find best match
        .find(
          (item) => item.page && item.page === currentPath.substring(0, item.page.length)
        );
      if (currentMenu) {
        items.push({
          text: currentMenu.title,
          page: currentMenu.page,
          icon: currentMenu.icon,
        });
        let path = currentMenu.page;
        currentPath
          .substring(currentMenu.page.length)
          .split("/")
          .forEach((item) => {
            if (item) {
              items.push({
                text: item,
                page: path + "/" + item,
              });
            }
          });
      }
      return items;
    },
  },
  methods: {
    flatMenuItems(menuItems) {
      const items = [];
      menuItems.forEach((item) => {
        items.push(item);
        if (item.items) {
          items.push(...this.flatMenuItems(item.items));
        }
      });
      return items;
    },
  },
};
</script>

<style scoped>
.layout {
  display: grid;
  grid-template-columns: min-content 1fr;
  grid-template-rows: 62px 1fr;
  grid-column-gap: 0px;
  grid-row-gap: 0px;
  width: 100vw;
  height: 100vh;
}

.layout > .sidebar {
  grid-area: 1 / 1 / 3 / 2;
}
.layout > .navbar {
  grid-area: 1 / 2 / 2 / 3;
}
.body {
  grid-area: 2 / 2 / 3 / 3;
  background-color: #f5f5f5;
  padding: 0.5rem;
  display: flex;
  flex-direction: column;
  overflow: auto;
}
</style>

<style>
.bg-primary {
  background-color: #1572e8 !important;
}
.disable-controls {
  pointer-events: none;
  opacity: 0.7;
}
</style>
