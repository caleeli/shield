<template>
  <b-pagination
    :total-rows="total"
    :per-page="perPage"
    first-number
    last-number
    prev-text="Prev"
    next-text="Sig"
    v-model="page"
    @change="navigate"
  ></b-pagination>
</template>
<script>
export default {
  props: {
    module: String,
    value: Number,
  },
  data() {
    return {
      page: this.value,
    };
  },
  computed: {
    total() {
      return this.$store.state[this.module]?.total ?? this.$parent[this.module].total;
    },
    perPage() {
      return this.$store.state[this.module]?.per_page ?? this.$parent[this.module].per_page;
    },
  },
  methods: {
    toggleSidebar() {
      this.$emit("toggle-sidebar");
    },
    navigate(page) {
      const capModule =
        this.module.charAt(0).toUpperCase() + this.module.slice(1);
      this.$emit("input", page);
      this.$parent[`navigate${capModule}`](page);
    },
  },
};
</script>
