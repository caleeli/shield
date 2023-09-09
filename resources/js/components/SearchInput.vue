<template>
  <b-form-group label-for="search">
    <b-input-group>
      <b-form-input
        id="search"
        placeholder="Buscar"
        v-model="search"
        @keypress.enter="navigate(1)"
      />
      <b-input-group-append>
        <b-button size="sm" variant="secondary" @click="navigate(1)">
          <i class="fas fa-search" />
        </b-button>
      </b-input-group-append>
    </b-input-group>
  </b-form-group>
</template>
<script>
export default {
  props: {
    module: {
      type: String,
      required: true,
    },
    value: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      search: this.value,
    };
  },
  methods: {
    navigate(page) {
      this.$emit("input", this.search);
      const capModule =
        this.module.charAt(0).toUpperCase() + this.module.slice(1);
      this.$parent[`navigate${capModule}`](page);
    },
  },
};
</script>
