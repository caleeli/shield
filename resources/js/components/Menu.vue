<template>
  <div>
    <div
      :key="item.title"
      :class="`d-flex align-items-center sidebar-item ${
        sidebarCollapsed ? 'collapsed' : ''
      } ${isCurrentPage(item) ? 'active' : ''}
      ${isSelectedParent(item) ? 'inside' : ''}`"
      @click="handleThisMenu(item)"
      aria-type="button"
      :aria-label="`menu-${item.title}`"
    >
      <i :class="`fas fa-${item.icon}`" class="mr-2"></i>
      <span v-show="!sidebarCollapsed">{{ item.title }}</span>
      <template v-if="item.items">
        <i
          :class="`fas fa-angle-${open ? 'right' : 'down'}`"
          class="ml-auto"
        />
      </template>
    </div>
    <div v-if="item.items && open" class="sidebar-items">
      <template v-for="item in item.items">
        <Menu
          :item="item"
          :sidebarCollapsed="sidebarCollapsed"
          @handleMenu="handleMenu"
        />
      </template>
    </div>
  </div>
</template>
<script>
import { mapState } from "vuex";

export default {
  name: "Menu",
  props: {
    item: {
      type: Object,
      required: true,
    },
    sidebarCollapsed: {
      type: Boolean,
      required: true,
    },
  },
  data() {
    return {
      open: !this.isSelectedParent(this.item),
    };
  },
  computed: {
    ...mapState({
      menuItems: (state) => state.menu.menuItems,
    }),
    currentItem() {
      return this.flatMenuItems(this.menuItems).filter(
        (item) =>
          item.page &&
          item.page === this.$route.path.substring(0, item.page.length)
      ).pop();
    }
  },
  components: {
    Menu: () => import("./Menu.vue"),
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
    isCurrentPage(item) {
      return this.currentItem === item;
    },
    isSelectedParent(item) {
      return item.items && item.items.includes(this.currentItem);
    },
    handleThisMenu(item) {
      if (item.items) {
        this.open = !this.open;
        return;
      }
      this.$emit("handleMenu", item);
    },
    handleMenu(item) {
      this.$emit("handleMenu", item);
    },
  },
};
</script>

<style>
.sidebar-item {
  height: 46px;
  margin: 4px 15px;
  padding: 8px 15px;
  border-radius: 0.5rem;
  cursor: pointer;
  color: black;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis !important;
}
.sidebar-item.active {
  color: white;
}
.sidebar-item.active {
  background-color: #1572e8;
  opacity: 1;
  box-shadow: 4px 4px 10px 0 rgb(0 0 0 / 10%),
    4px 4px 15px -5px rgb(21 114 232 / 40%);
}
.sidebar-item.inside {
  background-color: rgb(199 199 199 / 20%);
  opacity: 1;
  box-shadow: 4px 4px 10px 0 rgb(0 0 0 / 10%),
    4px 4px 15px -5px rgb(199 199 199 / 40%);
}
.icon-transparent {
  opacity: 0.1;
  font-size: smaller;
}
.sidebar-items {
  background-color: rgb(199 199 199 / 20%);
}
</style>
